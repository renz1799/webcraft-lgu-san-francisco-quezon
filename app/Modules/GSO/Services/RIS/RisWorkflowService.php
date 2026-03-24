<?php

namespace App\Modules\GSO\Services\RIS;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Models\RisItem;
use App\Modules\GSO\Models\StockMovement;
use App\Modules\GSO\Services\Contracts\Numbers\RisNumberServiceInterface;
use App\Modules\GSO\Services\Contracts\RIS\RisWorkflowServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RisWorkflowService implements RisWorkflowServiceInterface
{
    public function __construct(
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly RisNumberServiceInterface $risNumbers,
    ) {
    }

    public function submit(string $actorUserId, string $risId): Ris
    {
        return DB::transaction(function () use ($actorUserId, $risId) {
            $ris = $this->loadWorkflowRis($risId);

            abort_if((string) $ris->status !== 'draft', 409, 'Only draft RIS can be submitted.');
            $this->assertReadyForWorkflow($ris);

            $old = $ris->only(['status', 'submitted_by_name', 'submitted_at', 'rejected_by_name', 'rejected_at', 'rejected_reason']);
            $actorName = (string) (optional(DB::table('users')->where('id', $actorUserId)->first())->name ?? 'System');

            $ris->fill([
                'status' => 'submitted',
                'submitted_by_name' => $actorName,
                'submitted_at' => now(),
                'rejected_by_name' => null,
                'rejected_at' => null,
                'rejected_reason' => null,
            ]);
            $ris->save();

            $this->auditLogs->record(
                action: 'ris.submitted',
                subject: $ris,
                changesOld: $old,
                changesNew: $ris->toArray(),
                meta: [],
                message: 'RIS submitted: ' . $this->risLabel($ris->toArray()),
                display: $this->buildWorkflowTransitionDisplay($old, $ris->toArray(), 'RIS submitted', 'Workflow Status', $ris),
            );

            return $ris->refresh();
        });
    }

    public function approveIssue(string $actorUserId, string $risId): Ris
    {
        return DB::transaction(function () use ($actorUserId, $risId) {
            $ris = $this->loadWorkflowRis($risId);

            abort_if((string) $ris->status === 'issued', 409, 'RIS already issued.');
            abort_if((string) $ris->status !== 'submitted', 409, 'Only submitted RIS can be approved/issued.');
            $this->assertReadyForWorkflow($ris);

            $risFundSourceId = trim((string) ($ris->fund_source_id ?? ''));
            abort_if($risFundSourceId === '', 422, 'RIS has no Fund Source selected.');

            $alreadyIssuedMovements = DB::table('stock_movements')
                ->whereNull('deleted_at')
                ->where('reference_type', 'ris')
                ->where('reference_id', $risId)
                ->where('movement_type', 'issue')
                ->count();

            abort_if($alreadyIssuedMovements > 0, 409, 'RIS already issued (stock movements exist).');

            $items = $ris->items->map(fn (RisItem $item) => (object) [
                'id' => (string) $item->id,
                'item_id' => (string) $item->item_id,
                'qty_requested' => (int) ($item->qty_requested ?? 0),
            ]);

            abort_if($items->count() <= 0, 409, 'Cannot issue RIS with zero items.');

            $itemIds = $items->pluck('item_id')->map(fn ($value) => (string) $value)->values()->all();

            $stocks = DB::table('stocks')
                ->whereNull('deleted_at')
                ->where('fund_source_id', $risFundSourceId)
                ->whereIn('item_id', $itemIds)
                ->lockForUpdate()
                ->get(['id', 'item_id', 'fund_source_id', 'on_hand'])
                ->keyBy(fn ($row) => (string) $row->item_id);

            foreach ($items as $item) {
                $qty = (int) $item->qty_requested;
                $stock = $stocks->get((string) $item->item_id);

                abort_if($qty <= 0, 409, 'Invalid qty_requested detected.');
                abort_if(!$stock, 409, 'No stock record found for an item in this RIS fund source.');
                abort_if((int) ($stock->on_hand ?? 0) <= 0, 409, 'One or more items are out of stock.');
                abort_if($qty > (int) ($stock->on_hand ?? 0), 409, 'Requested quantity exceeds stock on hand for one or more items.');
            }

            $actorName = (string) (optional(DB::table('users')->where('id', $actorUserId)->first())->name ?? 'System');

            if (!trim((string) ($ris->ris_number ?? ''))) {
                $ris->ris_number = $this->risNumbers->generate($ris, now());
            }

            foreach ($items as $item) {
                $issueDate = $ris->issued_by_date ? $ris->issued_by_date->copy()->endOfDay() : now();

                DB::table('stocks')
                    ->whereNull('deleted_at')
                    ->where('item_id', (string) $item->item_id)
                    ->where('fund_source_id', $risFundSourceId)
                    ->update([
                        'on_hand' => DB::raw('on_hand - ' . (int) $item->qty_requested),
                        'updated_at' => now(),
                    ]);

                StockMovement::query()->create([
                    'item_id' => (string) $item->item_id,
                    'fund_source_id' => $risFundSourceId,
                    'movement_type' => 'issue',
                    'qty' => (int) $item->qty_requested,
                    'reference_type' => 'ris',
                    'reference_id' => $risId,
                    'air_item_id' => null,
                    'ris_item_id' => (string) $item->id,
                    'occurred_at' => $issueDate,
                    'created_by_name' => $actorName,
                    'remarks' => 'Issued via RIS approval. RIS No: ' . $ris->ris_number,
                ]);

                DB::table('ris_items')
                    ->where('id', (string) $item->id)
                    ->update([
                        'qty_issued' => (int) $item->qty_requested,
                        'updated_at' => now(),
                    ]);
            }

            $old = $ris->toArray();

            $ris->fill(['status' => 'issued']);
            $ris->save();
            $ris->load('items');

            $this->auditLogs->record(
                action: 'ris.issued',
                subject: $ris,
                changesOld: $old,
                changesNew: $ris->toArray(),
                meta: ['items_count' => $items->count()],
                message: 'RIS issued: ' . $this->risLabel($ris->toArray()),
                display: $this->buildIssuedDisplay($old, $ris->toArray(), $items->count(), $ris),
            );

            return $ris->refresh();
        });
    }

    public function reject(string $actorUserId, string $risId, ?string $reason = null): Ris
    {
        return DB::transaction(function () use ($actorUserId, $risId, $reason) {
            $ris = $this->loadWorkflowRis($risId);

            abort_if((string) $ris->status !== 'submitted', 409, 'Only submitted RIS can be rejected.');

            $actorName = (string) (optional(DB::table('users')->where('id', $actorUserId)->first())->name ?? 'System');
            $old = $ris->only(['status', 'rejected_by_name', 'rejected_at', 'rejected_reason']);

            $ris->fill([
                'status' => 'rejected',
                'rejected_by_name' => $actorName,
                'rejected_at' => now(),
                'rejected_reason' => $reason ? trim((string) $reason) : null,
            ]);
            $ris->save();

            $this->auditLogs->record(
                action: 'ris.rejected',
                subject: $ris,
                changesOld: $old,
                changesNew: $ris->toArray(),
                meta: [],
                message: 'RIS rejected: ' . $this->risLabel($ris->toArray()),
                display: $this->buildRejectedDisplay($old, $ris->toArray(), $ris),
            );

            return $ris->refresh();
        });
    }

    public function reopen(string $actorUserId, string $risId): Ris
    {
        return DB::transaction(function () use ($actorUserId, $risId) {
            $ris = $this->loadWorkflowRis($risId);

            abort_if(!in_array((string) $ris->status, ['submitted', 'rejected'], true), 409, 'Only submitted or rejected RIS can be reopened to draft.');

            $old = $ris->only(['status', 'submitted_by_name', 'submitted_at', 'rejected_by_name', 'rejected_at', 'rejected_reason']);

            $ris->fill([
                'status' => 'draft',
                'submitted_by_name' => null,
                'submitted_at' => null,
                'rejected_by_name' => null,
                'rejected_at' => null,
                'rejected_reason' => null,
            ]);
            $ris->save();

            $this->auditLogs->record(
                action: 'ris.reopened',
                subject: $ris,
                changesOld: $old,
                changesNew: $ris->toArray(),
                meta: ['reopened_by_user_id' => $actorUserId],
                message: 'RIS reopened to draft: ' . $this->risLabel($ris->toArray()),
                display: $this->buildWorkflowTransitionDisplay($old, $ris->toArray(), 'RIS reopened', 'Workflow Status', $ris),
            );

            return $ris->refresh();
        });
    }

    public function revertToDraft(string $actorUserId, string $risId): Ris
    {
        return DB::transaction(function () use ($actorUserId, $risId) {
            $ris = $this->loadWorkflowRis($risId);
            $displayRis = clone $ris;

            abort_if((string) $ris->status !== 'issued', 409, 'Only issued RIS can be reverted to draft.');

            $movements = StockMovement::query()
                ->whereNull('deleted_at')
                ->where('reference_type', 'ris')
                ->where('reference_id', $risId)
                ->where('movement_type', 'issue')
                ->get();

            abort_if($movements->count() <= 0, 409, 'No issue movements found for this RIS.');

            $itemIds = $movements->pluck('item_id')->map(fn ($value) => (string) $value)->values()->all();

            DB::table('stocks')
                ->whereNull('deleted_at')
                ->whereIn('item_id', $itemIds)
                ->lockForUpdate()
                ->get();

            $actorName = (string) (optional(DB::table('users')->where('id', $actorUserId)->first())->name ?? 'System');

            foreach ($movements as $movement) {
                $qty = (int) ($movement->qty ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $restoreStock = DB::table('stocks')
                    ->whereNull('deleted_at')
                    ->where('item_id', (string) $movement->item_id);

                $movementFundSourceId = trim((string) ($movement->fund_source_id ?? ''));
                if ($movementFundSourceId !== '') {
                    $restoreStock->where('fund_source_id', $movementFundSourceId);
                }

                $restoreStock->update([
                    'on_hand' => DB::raw('on_hand + ' . $qty),
                    'updated_at' => now(),
                ]);

                StockMovement::query()->create([
                    'item_id' => (string) $movement->item_id,
                    'fund_source_id' => $movementFundSourceId !== '' ? $movementFundSourceId : null,
                    'movement_type' => 'restore',
                    'qty' => $qty,
                    'reference_type' => 'ris',
                    'reference_id' => $risId,
                    'air_item_id' => null,
                    'ris_item_id' => trim((string) ($movement->ris_item_id ?? '')) !== '' ? (string) $movement->ris_item_id : null,
                    'occurred_at' => $ris->issued_by_date ? $ris->issued_by_date->copy()->endOfDay() : now(),
                    'created_by_name' => $actorName,
                    'remarks' => 'Restored stock due to RIS revert to draft. RIS No: ' . $ris->ris_number,
                ]);
            }

            StockMovement::query()
                ->whereNull('deleted_at')
                ->where('reference_type', 'ris')
                ->where('reference_id', $risId)
                ->where('movement_type', 'issue')
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('ris_items')
                ->where('ris_id', $risId)
                ->whereNull('deleted_at')
                ->update([
                    'qty_issued' => 0,
                    'updated_at' => now(),
                ]);

            $old = $ris->toArray();

            $ris->fill([
                'status' => 'draft',
                'rejected_by_name' => null,
                'rejected_at' => null,
                'rejected_reason' => null,
            ]);
            $ris->save();

            $this->auditLogs->record(
                action: 'ris.reverted_to_draft',
                subject: $ris,
                changesOld: $old,
                changesNew: $ris->toArray(),
                meta: ['restored_items' => $movements->count()],
                message: 'RIS reverted to draft: ' . $this->risLabel($ris->toArray()) . ' (stocks restored)',
                display: $this->buildRevertedDisplay($old, $ris->toArray(), $movements->count(), $displayRis),
            );

            return $ris->refresh();
        });
    }

    private function assertReadyForWorkflow(Ris $ris): void
    {
        $errors = [];

        if (!$ris->ris_date) {
            $errors['ris_date'][] = 'RIS Date is required.';
        }

        $fundSourceId = $this->nullableTrim((string) ($ris->fund_source_id ?? ''));
        if ($fundSourceId === null) {
            $errors['fund_source_id'][] = 'Fund Source is required.';
        } else {
            $isValidFundSource = DB::table('fund_sources')
                ->where('id', $fundSourceId)
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->exists();

            if (!$isValidFundSource) {
                $errors['fund_source_id'][] = 'The selected fund source is invalid or inactive.';
            }
        }

        $departmentId = $this->nullableTrim((string) ($ris->requesting_department_id ?? ''));
        if ($departmentId === null) {
            $errors['requesting_department_id'][] = 'Requesting Department is required.';
        } else {
            $isValidDepartment = DB::table('departments')
                ->where('id', $departmentId)
                ->whereNull('deleted_at')
                ->exists();

            if (!$isValidDepartment) {
                $errors['requesting_department_id'][] = 'The selected requesting department is invalid.';
            }
        }

        if ($this->nullableTrim((string) ($ris->purpose ?? '')) === null) {
            $errors['purpose'][] = 'Purpose is required.';
        }

        $requiredTextFields = [
            'requested_by_name' => 'Requested by Name',
            'requested_by_designation' => 'Requested by Designation',
            'approved_by_name' => 'Approved by Name',
            'approved_by_designation' => 'Approved by Designation',
            'issued_by_name' => 'Issued by Name',
            'issued_by_designation' => 'Issued by Designation',
            'received_by_name' => 'Received by Name',
            'received_by_designation' => 'Received by Designation',
        ];

        foreach ($requiredTextFields as $field => $label) {
            if ($this->nullableTrim((string) ($ris->{$field} ?? '')) === null) {
                $errors[$field][] = "{$label} is required.";
            }
        }

        $requiredDates = [
            'requested_by_date' => 'Requested by Date',
            'approved_by_date' => 'Approved by Date',
            'issued_by_date' => 'Issued By Date',
            'received_by_date' => 'Received By Date',
        ];

        foreach ($requiredDates as $field => $label) {
            if (!$ris->{$field}) {
                $errors[$field][] = "{$label} is required.";
            }
        }

        $itemsCount = DB::table('ris_items')
            ->where('ris_id', (string) $ris->id)
            ->whereNull('deleted_at')
            ->count();

        if ($itemsCount <= 0) {
            $errors['items'][] = 'Add at least one RIS item before continuing.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function loadWorkflowRis(string $risId): Ris
    {
        return Ris::query()
            ->lockForUpdate()
            ->with('items')
            ->findOrFail($risId);
    }

    private function buildWorkflowTransitionDisplay(array $before, array $after, string $summaryPrefix, string $sectionTitle, ?Ris $ris = null): array
    {
        $sections = [
            [
                'title' => $sectionTitle,
                'items' => [
                    [
                        'label' => 'Status',
                        'before' => $this->statusLabel($before['status'] ?? null),
                        'after' => $this->statusLabel($after['status'] ?? null),
                    ],
                    [
                        'label' => 'RIS Number',
                        'before' => $this->displayValue($before['ris_number'] ?? null),
                        'after' => $this->displayValue($after['ris_number'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $ris?->items?->count()),
        ];

        if ($ris && $ris->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($ris, 'Included Items');
        }

        return [
            'summary' => $summaryPrefix . ': ' . $this->risLabel($after),
            'subject_label' => $this->risLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildIssuedDisplay(array $before, array $after, int $itemsCount, ?Ris $ris = null): array
    {
        $sections = [
            [
                'title' => 'Workflow Status',
                'items' => [
                    [
                        'label' => 'Status',
                        'before' => $this->statusLabel($before['status'] ?? null),
                        'after' => $this->statusLabel($after['status'] ?? null),
                    ],
                    [
                        'label' => 'Items Issued',
                        'before' => 'None',
                        'after' => (string) $itemsCount,
                    ],
                    [
                        'label' => 'RIS Number',
                        'before' => $this->displayValue($before['ris_number'] ?? null),
                        'after' => $this->displayValue($after['ris_number'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $itemsCount),
        ];

        if ($ris && $ris->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($ris, 'Issued Items');
        }

        return [
            'summary' => 'RIS issued: ' . $this->risLabel($after),
            'subject_label' => $this->risLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildRejectedDisplay(array $before, array $after, ?Ris $ris = null): array
    {
        $sections = [
            [
                'title' => 'Workflow Status',
                'items' => [
                    [
                        'label' => 'Status',
                        'before' => $this->statusLabel($before['status'] ?? null),
                        'after' => $this->statusLabel($after['status'] ?? null),
                    ],
                    [
                        'label' => 'Rejected By',
                        'before' => $this->displayValue($before['rejected_by_name'] ?? null),
                        'after' => $this->displayValue($after['rejected_by_name'] ?? null),
                    ],
                    [
                        'label' => 'Rejected At',
                        'before' => $this->displayValue($before['rejected_at'] ?? null),
                        'after' => $this->displayValue($after['rejected_at'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $ris?->items?->count()),
        ];

        if ($ris && $ris->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($ris, 'Included Items');
        }

        $reason = $this->nullableTrim((string) ($after['rejected_reason'] ?? ''));
        if ($reason !== null) {
            $sections[] = [
                'title' => 'Rejection Reason',
                'items' => [
                    ['label' => 'Reason', 'value' => $reason],
                ],
            ];
        }

        return [
            'summary' => 'RIS rejected: ' . $this->risLabel($after),
            'subject_label' => $this->risLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildRevertedDisplay(array $before, array $after, int $restoredItems, ?Ris $ris = null): array
    {
        $sections = [
            [
                'title' => 'Workflow Status',
                'items' => [
                    [
                        'label' => 'Status',
                        'before' => $this->statusLabel($before['status'] ?? null),
                        'after' => $this->statusLabel($after['status'] ?? null),
                    ],
                    [
                        'label' => 'Restored Stock Movements',
                        'before' => 'None',
                        'after' => (string) $restoredItems,
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $ris?->items?->count()),
        ];

        if ($ris && $ris->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($ris, 'Restored Items');
        }

        return [
            'summary' => 'RIS reverted to draft: ' . $this->risLabel($after),
            'subject_label' => $this->risLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildDocumentContextSection(array $values, ?int $itemsCount = null): array
    {
        $items = [
            ['label' => 'Requesting Department', 'value' => $this->resolveDepartmentLabel($values)],
            ['label' => 'Fund Source', 'value' => $this->resolveFundSourceLabel($values)],
            ['label' => 'RIS Date', 'value' => $this->displayValue($values['ris_date'] ?? null)],
            ['label' => 'Purpose', 'value' => $this->displayValue($values['purpose'] ?? null)],
        ];

        if ($itemsCount !== null) {
            $items[] = ['label' => 'Items Count', 'value' => (string) $itemsCount];
        }

        return [
            'title' => 'Document Context',
            'items' => $items,
        ];
    }

    private function buildIncludedItemsSection(Ris $ris, string $title): array
    {
        $items = $ris->items
            ->take(10)
            ->map(function (RisItem $item): array {
                $parts = [];

                $qtyIssued = (int) ($item->qty_issued ?? 0);
                if ($qtyIssued > 0) {
                    $parts[] = 'Qty Issued: ' . $qtyIssued;
                } else {
                    $parts[] = 'Qty Requested: ' . max(1, (int) ($item->qty_requested ?? 1));
                }

                $unit = trim((string) ($item->unit_snapshot ?? ''));
                if ($unit !== '') {
                    $parts[] = 'Unit: ' . $unit;
                }

                $remarks = trim((string) ($item->remarks ?? ''));
                if ($remarks !== '') {
                    $parts[] = 'Remarks: ' . $remarks;
                }

                return [
                    'label' => $this->itemLabel($item),
                    'value' => implode(' | ', $parts),
                ];
            })
            ->values()
            ->all();

        return [
            'title' => $title,
            'items' => $items,
        ];
    }

    private function itemLabel(RisItem $item): string
    {
        $stockNo = trim((string) ($item->stock_no_snapshot ?? ''));
        $description = trim((string) ($item->description_snapshot ?? ''));

        if ($stockNo !== '' && $description !== '') {
            return $stockNo . ' - ' . $description;
        }

        if ($stockNo !== '') {
            return $stockNo;
        }

        if ($description !== '') {
            return $description;
        }

        return 'RIS Item';
    }

    private function risLabel(array $values): string
    {
        $number = trim((string) ($values['ris_number'] ?? ''));
        if ($number !== '') {
            return $number;
        }

        $status = $this->statusLabel($values['status'] ?? null);
        if ($status !== 'None') {
            return sprintf('RIS (%s)', $status);
        }

        return 'RIS';
    }

    private function resolveDepartmentLabel(array $values): string
    {
        $snapshotCode = trim((string) ($values['requesting_department_code_snapshot'] ?? ''));
        $snapshotName = trim((string) ($values['requesting_department_name_snapshot'] ?? ''));

        if ($snapshotCode !== '' && $snapshotName !== '') {
            return sprintf('%s (%s)', $snapshotCode, $snapshotName);
        }

        if ($snapshotCode !== '' || $snapshotName !== '') {
            return $snapshotCode !== '' ? $snapshotCode : $snapshotName;
        }

        $departmentId = trim((string) ($values['requesting_department_id'] ?? ''));
        if ($departmentId === '') {
            return 'None';
        }

        $department = DB::table('departments')
            ->where('id', $departmentId)
            ->whereNull('deleted_at')
            ->first(['code', 'name']);

        if (!$department) {
            return 'Unknown Department';
        }

        $code = trim((string) ($department->code ?? ''));
        $name = trim((string) ($department->name ?? ''));

        if ($code !== '' && $name !== '') {
            return sprintf('%s (%s)', $code, $name);
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Department');
    }

    private function resolveFundSourceLabel(array $values): string
    {
        $fund = trim((string) ($values['fund'] ?? ''));
        if ($fund !== '') {
            return $fund;
        }

        $fundSourceId = trim((string) ($values['fund_source_id'] ?? ''));
        if ($fundSourceId === '') {
            return 'None';
        }

        $fundSource = DB::table('fund_sources')
            ->where('id', $fundSourceId)
            ->whereNull('deleted_at')
            ->first(['code', 'name']);

        if (!$fundSource) {
            return 'Unknown Fund Source';
        }

        $code = trim((string) ($fundSource->code ?? ''));
        $name = trim((string) ($fundSource->name ?? ''));

        if ($code !== '' && $name !== '') {
            return sprintf('%s (%s)', $code, $name);
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Fund Source');
    }

    private function statusLabel(?string $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? ucfirst(str_replace('_', ' ', $value)) : 'None';
    }

    private function displayValue(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : 'None';
    }
}
