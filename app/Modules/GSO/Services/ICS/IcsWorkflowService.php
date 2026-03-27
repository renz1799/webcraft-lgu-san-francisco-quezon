<?php

namespace App\Modules\GSO\Services\ICS;

use App\Core\Models\Department;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Models\AccountableOfficer;
use App\Modules\GSO\Models\Ics;
use App\Modules\GSO\Models\IcsItem;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Services\Contracts\ICS\IcsWorkflowServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemEventServiceInterface;
use App\Modules\GSO\Services\Contracts\Numbers\IcsNumberServiceInterface;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryEventTypes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class IcsWorkflowService implements IcsWorkflowServiceInterface
{
    public function __construct(
        private readonly InventoryItemEventServiceInterface $events,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly IcsNumberServiceInterface $icsNumbers,
    ) {}

    public function submit(string $actorUserId, string $icsId): Ics
    {
        return DB::transaction(function () use ($icsId) {
            $ics = $this->loadWorkflowIcs($icsId);

            abort_if((string) $ics->status !== 'draft', 409, 'Only draft ICS can be submitted.');

            $this->assertReadyForWorkflow($ics);
            $this->assertFundClusterConsistency($ics);

            $old = $ics->toArray();
            $ics->status = 'submitted';
            $ics->save();

            $this->auditLogs->record(
                action: 'ics.submitted',
                subject: $ics,
                changesOld: $old,
                changesNew: $ics->toArray(),
                meta: [],
                message: 'ICS submitted: ' . $this->icsLabel($ics->toArray()),
                display: $this->buildWorkflowTransitionDisplay($old, $ics->toArray(), 'ICS submitted', 'Workflow Status', $ics),
            );

            return $ics->refresh();
        });
    }

    public function reopen(string $actorUserId, string $icsId): Ics
    {
        return DB::transaction(function () use ($actorUserId, $icsId) {
            $ics = $this->loadWorkflowIcs($icsId);

            abort_if((string) $ics->status !== 'submitted', 409, 'Only submitted ICS can be reopened.');

            $old = $ics->toArray();
            $ics->status = 'draft';
            $ics->save();

            $this->auditLogs->record(
                action: 'ics.reopened',
                subject: $ics,
                changesOld: $old,
                changesNew: $ics->toArray(),
                meta: [
                    'reopened_by_user_id' => $actorUserId,
                ],
                message: 'ICS reopened to draft: ' . $this->icsLabel($ics->toArray()),
                display: $this->buildWorkflowTransitionDisplay($old, $ics->toArray(), 'ICS reopened', 'Workflow Status', $ics),
            );

            return $ics->refresh();
        });
    }

    public function finalize(string $actorUserId, string $icsId): Ics
    {
        return DB::transaction(function () use ($actorUserId, $icsId) {
            $ics = $this->loadWorkflowIcs($icsId);

            abort_if(! in_array((string) $ics->status, ['draft', 'submitted'], true), 409, 'Only draft or submitted ICS can be finalized.');

            $this->assertReadyForWorkflow($ics);
            $this->assertFundClusterConsistency($ics);
            $this->assertItemsStillInGsoPool($ics);

            $old = $ics->toArray();

            if (empty($ics->issued_date)) {
                $ics->issued_date = now()->toDateString();
            }

            if (empty($ics->ics_number)) {
                $ics->ics_number = $this->icsNumbers->nextNumber($ics->issued_date ?? now());
            }

            $entityName = trim((string) config('print.entity_name', ''));
            if ($entityName === '') {
                $entityName = trim((string) config('app.name', ''));
            }

            $ics->entity_name_snapshot = $entityName !== '' ? $entityName : ($ics->entity_name_snapshot ?: null);
            $ics->fund_cluster_code_snapshot = $this->nullableTrim((string) ($ics->fundSource?->fundCluster?->code ?? ''));
            $ics->fund_cluster_name_snapshot = $this->nullableTrim((string) ($ics->fundSource?->fundCluster?->name ?? ''));
            $ics->save();

            $accountableOfficerId = $this->resolveAccountableOfficerId(
                (string) ($ics->received_by_name ?? ''),
                (string) ($ics->department_id ?? ''),
            );

            foreach ($ics->items as $icsItem) {
                /** @var InventoryItem|null $inventoryItem */
                $inventoryItem = $icsItem->inventoryItem;
                abort_if(! $inventoryItem, 422, 'ICS contains an invalid inventory item.');

                $quantity = max(1, (int) ($icsItem->quantity ?? 1));
                $status = trim((string) ($inventoryItem->status ?? '')) !== '' ? (string) $inventoryItem->status : 'serviceable';

                $this->events->create((string) $actorUserId, (string) $inventoryItem->id, [
                    'event_type' => InventoryEventTypes::ISSUED,
                    'event_date' => $ics->issued_date?->toDateString() ?? now()->toDateString(),
                    'quantity' => $quantity,
                    'department_id' => (string) $ics->department_id,
                    'fund_source_id' => (string) $ics->fund_source_id,
                    'person_accountable' => (string) ($ics->received_by_name ?? ''),
                    'office_snapshot' => $this->buildOfficeSnapshot($ics->department),
                    'officer_snapshot' => trim((string) ($ics->received_by_name ?? '')),
                    'unit_snapshot' => $icsItem->unit_snapshot ?? $inventoryItem->unit,
                    'amount_snapshot' => $icsItem->unit_cost_snapshot ?? $inventoryItem->acquisition_cost,
                    'status' => $status,
                    'condition' => $inventoryItem->condition,
                    'reference_type' => 'ICS',
                    'reference_no' => (string) $ics->ics_number,
                    'reference_id' => (string) $ics->id,
                ]);

                $inventoryItem->department_id = (string) $ics->department_id;
                $inventoryItem->accountable_officer = (string) ($ics->received_by_name ?? '');
                $inventoryItem->accountable_officer_id = $accountableOfficerId;
                $inventoryItem->custody_state = InventoryCustodyStates::ISSUED;
                $inventoryItem->status = $status;
                $inventoryItem->save();
            }

            $ics->status = 'finalized';
            $ics->save();

            $this->auditLogs->record(
                action: 'ics.finalized',
                subject: $ics,
                changesOld: $old,
                changesNew: $ics->toArray(),
                meta: ['items_count' => $ics->items->count()],
                message: 'ICS finalized for issuance: ' . $this->icsLabel($ics->toArray()),
                display: $this->buildFinalizedDisplay($old, $ics->toArray(), $ics),
            );

            return $ics->refresh();
        });
    }

    public function cancel(string $actorUserId, string $icsId, ?string $reason = null): Ics
    {
        return DB::transaction(function () use ($actorUserId, $icsId, $reason) {
            $ics = $this->loadWorkflowIcs($icsId);

            abort_if(! in_array((string) $ics->status, ['draft', 'submitted'], true), 409, 'Only draft or submitted ICS can be cancelled.');

            $old = $ics->toArray();
            $ics->status = 'cancelled';
            $ics->remarks = $reason ?: $ics->remarks;
            $ics->save();

            $this->auditLogs->record(
                action: 'ics.cancelled',
                subject: $ics,
                changesOld: $old,
                changesNew: $ics->toArray(),
                meta: [
                    'cancelled_by_user_id' => $actorUserId,
                    'reason' => $this->nullableTrim((string) ($reason ?? '')),
                ],
                message: $reason
                    ? 'ICS cancelled: ' . $this->icsLabel($ics->toArray()) . '. Reason: ' . trim((string) $reason)
                    : 'ICS cancelled: ' . $this->icsLabel($ics->toArray()),
                display: $this->buildCancelledDisplay($old, $ics->toArray(), $this->nullableTrim((string) ($reason ?? '')), $ics),
            );

            return $ics->refresh();
        });
    }

    private function assertReadyForWorkflow(Ics $ics): void
    {
        $errors = [];

        if (! $ics->department_id) {
            $errors['department_id'][] = 'Department is required.';
        }

        if (! $ics->fund_source_id) {
            $errors['fund_source_id'][] = 'Fund Source is required.';
        }

        if (! $ics->issued_date) {
            $errors['issued_date'][] = 'Issued Date is required.';
        }

        if ($this->nullableTrim((string) ($ics->received_from_name ?? '')) === null) {
            $errors['received_from_name'][] = 'Received from name is required.';
        }

        if ($this->nullableTrim((string) ($ics->received_from_position ?? '')) === null) {
            $errors['received_from_position'][] = 'Received from position is required.';
        }

        if ($this->nullableTrim((string) ($ics->received_from_office ?? '')) === null) {
            $errors['received_from_office'][] = 'Received from office is required.';
        }

        if (! $ics->received_from_date) {
            $errors['received_from_date'][] = 'Received from date is required.';
        }

        if ($this->nullableTrim((string) ($ics->received_by_name ?? '')) === null) {
            $errors['received_by_name'][] = 'Received by name is required.';
        }

        if ($this->nullableTrim((string) ($ics->received_by_position ?? '')) === null) {
            $errors['received_by_position'][] = 'Received by position is required.';
        }

        if ($this->nullableTrim((string) ($ics->received_by_office ?? '')) === null) {
            $errors['received_by_office'][] = 'Received by office is required.';
        }

        if (! $ics->received_by_date) {
            $errors['received_by_date'][] = 'Received by date is required.';
        }

        if ($ics->items->count() <= 0) {
            $errors['items'][] = 'Add at least one item before continuing.';
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors)->status(422);
        }
    }

    private function assertFundClusterConsistency(Ics $ics): void
    {
        $icsClusterId = trim((string) ($ics->fundSource?->fund_cluster_id ?? ''));
        if ($icsClusterId === '') {
            throw ValidationException::withMessages([
                'fund_source_id' => ['Selected Fund Source has no Fund Cluster.'],
            ])->status(422);
        }

        $mismatched = [];
        $ineligible = [];

        foreach ($ics->items as $icsItem) {
            /** @var InventoryItem|null $inventoryItem */
            $inventoryItem = $icsItem->inventoryItem;

            if (! $inventoryItem) {
                $mismatched[] = $this->formatItemLabel($icsItem, null) . ' - Missing inventory item record.';
                continue;
            }

            if ((bool) $inventoryItem->is_ics !== true) {
                $ineligible[] = $this->formatItemLabel($icsItem, $inventoryItem) . ' - Inventory item is no longer marked for ICS.';
                continue;
            }

            $itemClusterId = trim((string) ($inventoryItem->fundSource?->fund_cluster_id ?? ''));
            if ($itemClusterId === '' || $itemClusterId !== $icsClusterId) {
                $mismatched[] = $this->formatItemLabel($icsItem, $inventoryItem);
            }
        }

        $errors = [];
        if ($mismatched !== []) {
            $errors['fund_cluster_mismatch'] = $mismatched;
        }
        if ($ineligible !== []) {
            $errors['ineligible_items'] = $ineligible;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors)->status(422);
        }
    }

    private function assertItemsStillInGsoPool(Ics $ics): void
    {
        $notInPool = [];

        foreach ($ics->items as $icsItem) {
            /** @var InventoryItem|null $inventoryItem */
            $inventoryItem = $icsItem->inventoryItem;
            if (! $inventoryItem) {
                $notInPool[] = $this->formatItemLabel($icsItem, null) . ' - Missing inventory item record.';
                continue;
            }

            $reason = $this->getPoolIneligibilityReason($inventoryItem);
            if ($reason !== null) {
                $notInPool[] = $this->formatItemLabel($icsItem, $inventoryItem) . ' - ' . $reason;
            }
        }

        if ($notInPool !== []) {
            throw ValidationException::withMessages([
                'not_in_pool' => $notInPool,
            ])->status(422);
        }
    }

    private function loadWorkflowIcs(string $icsId): Ics
    {
        /** @var Ics $ics */
        $ics = Ics::query()
            ->lockForUpdate()
            ->with([
                'department',
                'fundSource.fundCluster',
                'items.inventoryItem.fundSource.fundCluster',
                'items.inventoryItem.item',
                'items.inventoryItem.latestEvent',
            ])
            ->findOrFail($icsId);

        return $ics;
    }

    private function buildWorkflowTransitionDisplay(
        array $before,
        array $after,
        string $summaryPrefix,
        string $sectionTitle,
        ?Ics $ics = null,
    ): array {
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
                        'label' => 'ICS Number',
                        'before' => $this->displayValue($before['ics_number'] ?? null),
                        'after' => $this->displayValue($after['ics_number'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $ics?->items?->count()),
        ];

        if ($ics && $ics->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($ics, 'Included Items');
        }

        return [
            'summary' => $summaryPrefix . ': ' . $this->icsLabel($after),
            'subject_label' => $this->icsLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildFinalizedDisplay(array $before, array $after, Ics $ics): array
    {
        $itemsCount = $ics->items->count();

        return [
            'summary' => 'ICS finalized for issuance: ' . $this->icsLabel($after),
            'subject_label' => $this->icsLabel($after),
            'sections' => [
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
                            'label' => 'ICS Number',
                            'before' => $this->displayValue($before['ics_number'] ?? null),
                            'after' => $this->displayValue($after['ics_number'] ?? null),
                        ],
                    ],
                ],
                $this->buildDocumentContextSection($after, $itemsCount),
                $this->buildIncludedItemsSection($ics, 'Issued Items'),
            ],
        ];
    }

    private function buildCancelledDisplay(array $before, array $after, ?string $reason, ?Ics $ics = null): array
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
                        'label' => 'ICS Number',
                        'before' => $this->displayValue($before['ics_number'] ?? null),
                        'after' => $this->displayValue($after['ics_number'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $ics?->items?->count()),
        ];

        if ($ics && $ics->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($ics, 'Included Items');
        }

        if ($reason !== null) {
            $sections[] = [
                'title' => 'Cancellation Reason',
                'items' => [[
                    'label' => 'Reason',
                    'value' => $reason,
                ]],
            ];
        }

        return [
            'summary' => 'ICS cancelled: ' . $this->icsLabel($after),
            'subject_label' => $this->icsLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildDocumentContextSection(array $values, ?int $itemsCount = null): array
    {
        $items = [
            [
                'label' => 'Department',
                'value' => $this->resolveDepartmentLabel($values['department_id'] ?? null),
            ],
            [
                'label' => 'Fund Source',
                'value' => $this->resolveFundSourceLabel($values['fund_source_id'] ?? null),
            ],
            [
                'label' => 'Issued Date',
                'value' => $this->displayValue($values['issued_date'] ?? null),
            ],
            [
                'label' => 'Received From',
                'value' => $this->personSummary(
                    $values['received_from_name'] ?? null,
                    $values['received_from_position'] ?? null,
                    $values['received_from_office'] ?? null,
                ),
            ],
            [
                'label' => 'Received By',
                'value' => $this->personSummary(
                    $values['received_by_name'] ?? null,
                    $values['received_by_position'] ?? null,
                    $values['received_by_office'] ?? null,
                ),
            ],
        ];

        if ($itemsCount !== null) {
            $items[] = [
                'label' => 'Items Count',
                'value' => (string) $itemsCount,
            ];
        }

        return [
            'title' => 'Document Context',
            'items' => $items,
        ];
    }

    private function buildIncludedItemsSection(Ics $ics, string $title): array
    {
        $items = $ics->items
            ->take(10)
            ->map(function (IcsItem $icsItem): array {
                $parts = [
                    'Qty: ' . max(1, (int) ($icsItem->quantity ?? 1)),
                ];

                $unit = trim((string) ($icsItem->unit_snapshot ?? ''));
                if ($unit !== '') {
                    $parts[] = 'Unit: ' . $unit;
                }

                $unitCost = $icsItem->unit_cost_snapshot;
                if ($unitCost !== null && $unitCost !== '') {
                    $parts[] = 'Unit Cost: ' . number_format((float) $unitCost, 2);
                }

                $totalCost = $icsItem->total_cost_snapshot;
                if ($totalCost !== null && $totalCost !== '') {
                    $parts[] = 'Total Cost: ' . number_format((float) $totalCost, 2);
                }

                $usefulLife = trim((string) ($icsItem->estimated_useful_life_snapshot ?? ''));
                if ($usefulLife !== '') {
                    $parts[] = 'Useful Life: ' . $usefulLife;
                }

                return [
                    'label' => $this->formatItemLabel($icsItem, $icsItem->inventoryItem),
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

    private function formatItemLabel(IcsItem $icsItem, ?InventoryItem $inventoryItem): string
    {
        $inventoryNo = trim((string) ($icsItem->inventory_item_no_snapshot ?? $inventoryItem?->property_number ?? $inventoryItem?->stock_number ?? '-'));
        $itemName = trim((string) ($icsItem->item_name_snapshot ?? $inventoryItem?->item?->item_name ?? '-'));

        return trim($inventoryNo . ' - ' . $itemName);
    }

    private function buildOfficeSnapshot(?Department $department): string
    {
        if (! $department) {
            return '';
        }

        $shortName = trim((string) ($department->short_name ?? ''));
        if ($shortName !== '') {
            return $shortName;
        }

        $code = trim((string) ($department->code ?? ''));
        if ($code !== '') {
            return $code;
        }

        return trim((string) ($department->name ?? ''));
    }

    private function resolveAccountableOfficerId(string $fullName, string $departmentId): ?string
    {
        $fullName = trim($fullName);
        $departmentId = trim($departmentId);

        if ($fullName === '' || $departmentId === '') {
            return null;
        }

        $normalizedName = Str::lower(preg_replace('/\s+/', ' ', $fullName) ?? $fullName);

        $officer = AccountableOfficer::query()
            ->where('department_id', $departmentId)
            ->where('is_active', true)
            ->where(function ($query) use ($fullName, $normalizedName) {
                $query->whereRaw('LOWER(full_name) = ?', [Str::lower($fullName)])
                    ->orWhere('normalized_name', $normalizedName);
            })
            ->orderBy('full_name')
            ->first(['id']);

        return $officer?->id ? (string) $officer->id : null;
    }

    private function getPoolIneligibilityReason(InventoryItem $item): ?string
    {
        if ($item->trashed()) {
            return 'Item is archived.';
        }

        $status = Str::lower(trim((string) ($item->status ?? '')));
        if (in_array($status, ['disposed', 'lost'], true)) {
            return 'Item is no longer available.';
        }

        $poolDepartmentId = $this->resolvePoolDepartmentId();
        $currentDepartmentId = trim((string) ($item->latestEvent?->department_id ?? $item->department_id ?? ''));
        if ($poolDepartmentId !== '' && $currentDepartmentId !== $poolDepartmentId) {
            return 'Item is not currently assigned to the GSO pool.';
        }

        if ((string) ($item->custody_state ?? '') !== InventoryCustodyStates::POOL) {
            return 'Item is no longer in pool custody.';
        }

        return null;
    }

    private function resolvePoolDepartmentId(): string
    {
        $configuredId = trim((string) config('gso.pool.department_id', ''));
        if ($configuredId !== '') {
            return $configuredId;
        }

        $configuredCode = trim((string) config('gso.pool.department_code', 'GSO'));
        if ($configuredCode === '') {
            return '';
        }

        $department = Department::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(code) = ?', [Str::lower($configuredCode)])
            ->first(['id']);

        return $department?->id ? (string) $department->id : '';
    }

    private function icsLabel(array $values): string
    {
        $number = trim((string) ($values['ics_number'] ?? ''));
        if ($number !== '') {
            return $number;
        }

        $status = $this->statusLabel($values['status'] ?? null);
        if ($status !== 'None') {
            return sprintf('ICS (%s)', $status);
        }

        return 'ICS';
    }

    private function resolveDepartmentLabel(?string $departmentId): string
    {
        $departmentId = trim((string) ($departmentId ?? ''));
        if ($departmentId === '') {
            return 'None';
        }

        $department = DB::table('departments')
            ->where('id', $departmentId)
            ->whereNull('deleted_at')
            ->first(['code', 'name']);

        if (! $department) {
            return 'Unknown Department';
        }

        $code = trim((string) ($department->code ?? ''));
        $name = trim((string) ($department->name ?? ''));

        if ($code !== '' && $name !== '') {
            return sprintf('%s (%s)', $code, $name);
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Department');
    }

    private function resolveFundSourceLabel(?string $fundSourceId): string
    {
        $fundSourceId = trim((string) ($fundSourceId ?? ''));
        if ($fundSourceId === '') {
            return 'None';
        }

        $fundSource = DB::table('fund_sources')
            ->where('id', $fundSourceId)
            ->whereNull('deleted_at')
            ->first(['code', 'name']);

        if (! $fundSource) {
            return 'Unknown Fund Source';
        }

        $code = trim((string) ($fundSource->code ?? ''));
        $name = trim((string) ($fundSource->name ?? ''));

        if ($code !== '' && $name !== '') {
            return sprintf('%s (%s)', $code, $name);
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Fund Source');
    }

    private function personSummary(?string $name, ?string $position, ?string $office): string
    {
        $parts = array_values(array_filter([
            $this->nullableTrim((string) ($name ?? '')),
            $this->nullableTrim((string) ($position ?? '')),
            $this->nullableTrim((string) ($office ?? '')),
        ]));

        return $parts === [] ? 'None' : implode(' | ', $parts);
    }

    private function statusLabel(?string $value): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return 'None';
        }

        return ucfirst(str_replace('_', ' ', $value));
    }

    private function displayValue(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : 'None';
    }

    private function nullableTrim(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
