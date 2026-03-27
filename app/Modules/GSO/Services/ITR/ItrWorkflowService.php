<?php

namespace App\Modules\GSO\Services\ITR;

use App\Core\Models\Department;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Models\AccountableOfficer;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\Itr;
use App\Modules\GSO\Models\ItrItem;
use App\Modules\GSO\Services\Contracts\InventoryItemEventServiceInterface;
use App\Modules\GSO\Services\Contracts\Numbers\ItrNumberServiceInterface;
use App\Modules\GSO\Services\Contracts\ITR\ItrWorkflowServiceInterface;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryEventTypes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ItrWorkflowService implements ItrWorkflowServiceInterface
{
    public function __construct(
        private readonly InventoryItemEventServiceInterface $events,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly ItrNumberServiceInterface $itrNumbers,
    ) {}

    public function submit(string $actorUserId, string $itrId): Itr
    {
        return DB::transaction(function () use ($itrId) {
            $itr = $this->loadWorkflowItr($itrId);

            abort_if((string) $itr->status !== 'draft', 409, 'Only draft ITR can be submitted.');

            $this->assertReadyForWorkflow($itr);
            $this->assertItemsStillTransferable($itr);

            $old = $itr->toArray();
            $itr->status = 'submitted';
            $itr->save();

            $this->auditLogs->record(
                'itr.submitted',
                $itr,
                $old,
                $itr->toArray(),
                [],
                'ITR submitted: ' . $this->itrLabel($itr->toArray()),
                $this->buildWorkflowTransitionDisplay($old, $itr->toArray(), 'ITR submitted', 'Workflow Status', $itr)
            );

            return $itr->refresh();
        });
    }

    public function reopen(string $actorUserId, string $itrId): Itr
    {
        return DB::transaction(function () use ($actorUserId, $itrId) {
            $itr = $this->loadWorkflowItr($itrId);

            abort_if((string) $itr->status !== 'submitted', 409, 'Only submitted ITR can be reopened.');

            $old = $itr->toArray();
            $itr->status = 'draft';
            $itr->save();

            $this->auditLogs->record(
                'itr.reopened',
                $itr,
                $old,
                $itr->toArray(),
                [
                    'reopened_by_user_id' => $actorUserId,
                ],
                'ITR reopened to draft: ' . $this->itrLabel($itr->toArray()),
                $this->buildWorkflowTransitionDisplay($old, $itr->toArray(), 'ITR reopened', 'Workflow Status', $itr)
            );

            return $itr->refresh();
        });
    }

    public function finalize(string $actorUserId, string $itrId): Itr
    {
        return DB::transaction(function () use ($actorUserId, $itrId) {
            $itr = $this->loadWorkflowItr($itrId);

            abort_if(!in_array((string) $itr->status, ['draft', 'submitted'], true), 409, 'Only draft or submitted ITR can be finalized.');

            $this->assertReadyForWorkflow($itr);
            $this->assertItemsStillTransferable($itr);

            $old = $itr->toArray();

            if (empty($itr->transfer_date)) {
                $itr->transfer_date = now()->toDateString();
            }

            if (empty($itr->itr_number)) {
                $itr->itr_number = $this->itrNumbers->nextNumber($itr->transfer_date ?? now());
            }

            $entityName = trim((string) config('print.entity_name', ''));
            if ($entityName === '') {
                $entityName = trim((string) config('app.name', ''));
            }

            $fromClusterCode = $this->nullableTrim((string) ($itr->fromFundSource?->fundCluster?->code ?? ''));
            $toClusterCode = $this->nullableTrim((string) ($itr->toFundSource?->fundCluster?->code ?? ''));

            $itr->entity_name_snapshot = $entityName !== '' ? $entityName : ($itr->entity_name_snapshot ?: null);
            $itr->header_fund_cluster_code_snapshot = $fromClusterCode ?? $toClusterCode;
            $itr->from_department_snapshot = $this->buildOfficeSnapshot($itr->fromDepartment);
            $itr->from_fund_cluster_code_snapshot = $fromClusterCode;
            $itr->to_department_snapshot = $this->buildOfficeSnapshot($itr->toDepartment);
            $itr->to_fund_cluster_code_snapshot = $toClusterCode;
            $itr->save();

            $toAccountableOfficerId = $this->resolveAccountableOfficerId(
                (string) ($itr->to_accountable_officer ?? ''),
                (string) ($itr->to_department_id ?? ''),
            );

            foreach ($itr->items as $itrItem) {
                $inventoryItem = $itrItem->inventoryItem;
                abort_if(!$inventoryItem, 422, 'ITR contains an invalid inventory item.');

                $quantity = max(1, (int) ($inventoryItem->quantity ?? $itrItem->quantity ?? 1));
                $eventDate = $itr->transfer_date?->toDateString() ?? now()->toDateString();
                $referenceNo = (string) ($itr->itr_number ?? '');

                $this->events->create((string) $actorUserId, (string) $inventoryItem->id, [
                    'event_type' => InventoryEventTypes::TRANSFERRED_OUT,
                    'event_date' => $eventDate,
                    'quantity' => $quantity,
                    'department_id' => (string) $itr->from_department_id,
                    'fund_source_id' => (string) $itr->from_fund_source_id,
                    'person_accountable' => (string) ($itr->from_accountable_officer ?? ''),
                    'office_snapshot' => $this->buildOfficeSnapshot($itr->fromDepartment),
                    'officer_snapshot' => (string) ($itr->from_accountable_officer ?? ''),
                    'unit_snapshot' => $inventoryItem->unit,
                    'amount_snapshot' => $itrItem->amount_snapshot ?? $inventoryItem->acquisition_cost,
                    'status' => $inventoryItem->status,
                    'condition' => $inventoryItem->condition,
                    'reference_type' => 'ITR',
                    'reference_no' => $referenceNo,
                    'reference_id' => (string) $itr->id,
                ]);

                $this->events->create((string) $actorUserId, (string) $inventoryItem->id, [
                    'event_type' => InventoryEventTypes::TRANSFERRED_IN,
                    'event_date' => $eventDate,
                    'quantity' => $quantity,
                    'department_id' => (string) $itr->to_department_id,
                    'fund_source_id' => (string) $itr->to_fund_source_id,
                    'person_accountable' => (string) ($itr->to_accountable_officer ?? ''),
                    'office_snapshot' => $this->buildOfficeSnapshot($itr->toDepartment),
                    'officer_snapshot' => (string) ($itr->to_accountable_officer ?? ''),
                    'unit_snapshot' => $inventoryItem->unit,
                    'amount_snapshot' => $itrItem->amount_snapshot ?? $inventoryItem->acquisition_cost,
                    'status' => $inventoryItem->status,
                    'condition' => $inventoryItem->condition,
                    'reference_type' => 'ITR',
                    'reference_no' => $referenceNo,
                    'reference_id' => (string) $itr->id,
                ]);

                $inventoryItem->department_id = (string) $itr->to_department_id;
                $inventoryItem->fund_source_id = (string) $itr->to_fund_source_id;
                $inventoryItem->accountable_officer = (string) ($itr->to_accountable_officer ?? '');
                $inventoryItem->accountable_officer_id = $toAccountableOfficerId;
                $inventoryItem->custody_state = InventoryCustodyStates::ISSUED;
                $inventoryItem->save();
            }

            $itr->status = 'finalized';
            $itr->save();

            $this->auditLogs->record(
                'itr.finalized',
                $itr,
                $old,
                $itr->toArray(),
                [
                    'items_count' => $itr->items->count(),
                ],
                'ITR finalized for transfer: ' . $this->itrLabel($itr->toArray()),
                $this->buildFinalizedDisplay($old, $itr->toArray(), $itr)
            );

            return $itr->refresh();
        });
    }

    public function cancel(string $actorUserId, string $itrId, ?string $reason = null): Itr
    {
        return DB::transaction(function () use ($actorUserId, $itrId, $reason) {
            $itr = $this->loadWorkflowItr($itrId);

            abort_if(!in_array((string) $itr->status, ['draft', 'submitted'], true), 409, 'Only draft or submitted ITR can be cancelled.');

            $old = $itr->toArray();
            $itr->status = 'cancelled';
            if ($this->nullableTrim((string) ($reason ?? '')) !== null) {
                $itr->remarks = trim((string) $reason);
            }
            $itr->save();

            $this->auditLogs->record(
                'itr.cancelled',
                $itr,
                $old,
                $itr->toArray(),
                [
                    'cancelled_by_user_id' => $actorUserId,
                    'reason' => $this->nullableTrim((string) ($reason ?? '')),
                ],
                $reason
                    ? 'ITR cancelled: ' . $this->itrLabel($itr->toArray()) . '. Reason: ' . trim((string) $reason)
                    : 'ITR cancelled: ' . $this->itrLabel($itr->toArray()),
                $this->buildCancelledDisplay($old, $itr->toArray(), $this->nullableTrim((string) ($reason ?? '')), $itr)
            );

            return $itr->refresh();
        });
    }

    private function loadWorkflowItr(string $itrId): Itr
    {
        return Itr::query()
            ->lockForUpdate()
            ->with([
                'fromDepartment',
                'toDepartment',
                'fromFundSource.fundCluster',
                'toFundSource.fundCluster',
                'items.inventoryItem.fundSource.fundCluster',
                'items.inventoryItem.item',
            ])
            ->findOrFail($itrId);
    }

    private function buildWorkflowTransitionDisplay(
        array $before,
        array $after,
        string $summaryPrefix,
        string $sectionTitle,
        ?Itr $itr = null
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
                        'label' => 'ITR Number',
                        'before' => $this->displayValue($before['itr_number'] ?? null),
                        'after' => $this->displayValue($after['itr_number'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $itr?->items?->count()),
        ];

        if ($itr && $itr->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($itr, 'Included Items');
        }

        return [
            'summary' => $summaryPrefix . ': ' . $this->itrLabel($after),
            'subject_label' => $this->itrLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildFinalizedDisplay(array $before, array $after, Itr $itr): array
    {
        $itemsCount = $itr->items->count();

        return [
            'summary' => 'ITR finalized for transfer: ' . $this->itrLabel($after),
            'subject_label' => $this->itrLabel($after),
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
                            'label' => 'Items Transferred',
                            'before' => 'None',
                            'after' => (string) $itemsCount,
                        ],
                        [
                            'label' => 'ITR Number',
                            'before' => $this->displayValue($before['itr_number'] ?? null),
                            'after' => $this->displayValue($after['itr_number'] ?? null),
                        ],
                    ],
                ],
                $this->buildDocumentContextSection($after, $itemsCount),
                $this->buildIncludedItemsSection($itr, 'Transferred Items'),
            ],
        ];
    }

    private function buildCancelledDisplay(array $before, array $after, ?string $reason, ?Itr $itr = null): array
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
                        'label' => 'ITR Number',
                        'before' => $this->displayValue($before['itr_number'] ?? null),
                        'after' => $this->displayValue($after['itr_number'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $itr?->items?->count()),
        ];

        if ($itr && $itr->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($itr, 'Included Items');
        }

        if ($reason !== null) {
            $sections[] = [
                'title' => 'Cancellation Reason',
                'items' => [
                    [
                        'label' => 'Reason',
                        'value' => $reason,
                    ],
                ],
            ];
        }

        return [
            'summary' => 'ITR cancelled: ' . $this->itrLabel($after),
            'subject_label' => $this->itrLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildDocumentContextSection(array $values, ?int $itemsCount = null): array
    {
        $items = [
            [
                'label' => 'From Department',
                'value' => $this->resolveDepartmentLabel($values['from_department_id'] ?? null),
            ],
            [
                'label' => 'To Department',
                'value' => $this->resolveDepartmentLabel($values['to_department_id'] ?? null),
            ],
            [
                'label' => 'Transfer Date',
                'value' => $this->displayValue($values['transfer_date'] ?? null),
            ],
            [
                'label' => 'Transfer Type',
                'value' => $this->transferTypeLabel($values['transfer_type'] ?? null, $values['transfer_type_other'] ?? null),
            ],
            [
                'label' => 'To Accountable Officer',
                'value' => $this->displayValue($values['to_accountable_officer'] ?? null),
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

    private function buildIncludedItemsSection(Itr $itr, string $title): array
    {
        $items = $itr->items
            ->take(10)
            ->map(function (ItrItem $item) {
                $parts = [
                    'Qty: ' . max(1, (int) ($item->quantity ?? 1)),
                ];

                $estimated = trim((string) ($item->estimated_useful_life_snapshot ?? ''));
                if ($estimated !== '') {
                    $parts[] = 'Useful Life: ' . $estimated;
                }

                $condition = trim((string) ($item->condition_snapshot ?? ''));
                if ($condition !== '') {
                    $parts[] = 'Condition: ' . ucfirst(str_replace('_', ' ', $condition));
                }

                $amount = $item->amount_snapshot;
                if ($amount !== null && $amount !== '') {
                    $parts[] = 'Amount: ' . number_format((float) $amount, 2);
                }

                return [
                    'label' => $this->formatItemLabel($item, $item->inventoryItem),
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

    private function itrLabel(array $values): string
    {
        $number = trim((string) ($values['itr_number'] ?? ''));
        if ($number !== '') {
            return $number;
        }

        $status = $this->statusLabel($values['status'] ?? null);
        if ($status !== 'None') {
            return sprintf('ITR (%s)', $status);
        }

        return 'ITR';
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

    private function transferTypeLabel(?string $type, ?string $other = null): string
    {
        $type = trim((string) ($type ?? ''));
        if ($type === '') {
            return 'None';
        }

        if ($type === 'others') {
            $other = trim((string) ($other ?? ''));
            return $other !== '' ? 'Others: ' . $other : 'Others';
        }

        return ucfirst(str_replace('_', ' ', $type));
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

    private function assertReadyForWorkflow(Itr $itr): void
    {
        $errors = [];

        if (!$itr->transfer_date) {
            $errors['transfer_date'][] = 'Transfer Date is required.';
        }

        if (!$itr->from_department_id) {
            $errors['from_department_id'][] = 'From Department is required.';
        }

        if ($this->nullableTrim((string) ($itr->from_accountable_officer ?? '')) === null) {
            $errors['from_accountable_officer'][] = 'From Accountable Officer is required.';
        }

        if (!$itr->from_fund_source_id) {
            $errors['from_fund_source_id'][] = 'From Fund Source is required.';
        } elseif ($this->nullableTrim((string) ($itr->fromFundSource?->fundCluster?->id ?? '')) === null) {
            $errors['from_fund_source_id'][] = 'Selected source Fund Source has no Fund Cluster.';
        }

        if (!$itr->to_department_id) {
            $errors['to_department_id'][] = 'To Department is required.';
        }

        if ($this->nullableTrim((string) ($itr->to_accountable_officer ?? '')) === null) {
            $errors['to_accountable_officer'][] = 'To Accountable Officer is required.';
        }

        if (!$itr->to_fund_source_id) {
            $errors['to_fund_source_id'][] = 'To Fund Source is required.';
        } elseif ($this->nullableTrim((string) ($itr->toFundSource?->fundCluster?->id ?? '')) === null) {
            $errors['to_fund_source_id'][] = 'Selected destination Fund Source has no Fund Cluster.';
        }

        if ($this->nullableTrim((string) ($itr->transfer_type ?? '')) === null) {
            $errors['transfer_type'][] = 'Transfer Type is required.';
        }

        if ((string) $itr->transfer_type === 'others' && $this->nullableTrim((string) ($itr->transfer_type_other ?? '')) === null) {
            $errors['transfer_type_other'][] = 'Specify the transfer type when Others is selected.';
        }

        if ($this->nullableTrim((string) ($itr->reason_for_transfer ?? '')) === null) {
            $errors['reason_for_transfer'][] = 'Reason for Transfer is required.';
        }

        if ($this->nullableTrim((string) ($itr->approved_by_name ?? '')) === null) {
            $errors['approved_by_name'][] = 'Approved By printed name is required.';
        }
        if ($this->nullableTrim((string) ($itr->approved_by_designation ?? '')) === null) {
            $errors['approved_by_designation'][] = 'Approved By designation is required.';
        }
        if (!$itr->approved_by_date) {
            $errors['approved_by_date'][] = 'Approved By date is required.';
        }

        if ($this->nullableTrim((string) ($itr->released_by_name ?? '')) === null) {
            $errors['released_by_name'][] = 'Released / Issued By printed name is required.';
        }
        if ($this->nullableTrim((string) ($itr->released_by_designation ?? '')) === null) {
            $errors['released_by_designation'][] = 'Released / Issued By designation is required.';
        }
        if (!$itr->released_by_date) {
            $errors['released_by_date'][] = 'Released / Issued By date is required.';
        }

        if ($this->nullableTrim((string) ($itr->received_by_name ?? '')) === null) {
            $errors['received_by_name'][] = 'Received By printed name is required.';
        }
        if ($this->nullableTrim((string) ($itr->received_by_designation ?? '')) === null) {
            $errors['received_by_designation'][] = 'Received By designation is required.';
        }
        if (!$itr->received_by_date) {
            $errors['received_by_date'][] = 'Received By date is required.';
        }

        if ($itr->items->count() <= 0) {
            $errors['items'][] = 'Add at least one item before continuing.';
        }

        if (
            (string) $itr->from_department_id !== ''
            && (string) $itr->to_department_id !== ''
            && (string) $itr->from_department_id === (string) $itr->to_department_id
            && mb_strtolower((string) ($this->nullableTrim((string) ($itr->from_accountable_officer ?? '')) ?? '')) === mb_strtolower((string) ($this->nullableTrim((string) ($itr->to_accountable_officer ?? '')) ?? ''))
        ) {
            $errors['to_accountable_officer'][] = 'Destination must differ from the current accountable assignment.';
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function assertItemsStillTransferable(Itr $itr): void
    {
        $errors = [];
        $sourceMismatch = [];
        $ineligible = [];

        $fromDeptId = trim((string) ($itr->from_department_id ?? ''));
        $fromClusterId = trim((string) ($itr->fromFundSource?->fund_cluster_id ?? ''));
        $fromOfficer = mb_strtolower((string) ($this->nullableTrim((string) ($itr->from_accountable_officer ?? '')) ?? ''));

        foreach ($itr->items as $itrItem) {
            $inventoryItem = $itrItem->inventoryItem;

            if (!$inventoryItem) {
                $ineligible[] = $this->formatItemLabel($itrItem, null) . ' - Missing inventory item record.';
                continue;
            }

            if ((bool) $inventoryItem->is_ics !== true) {
                $ineligible[] = $this->formatItemLabel($itrItem, $inventoryItem) . ' - Item is not tagged as ICS/semi-expendable.';
                continue;
            }

            $reason = $this->getIssuedIneligibilityReason($inventoryItem);
            $currentDeptId = trim((string) ($inventoryItem->department_id ?? ''));
            $currentOfficer = mb_strtolower(trim((string) ($inventoryItem->accountable_officer ?? '')));
            $currentClusterId = trim((string) ($inventoryItem->fundSource?->fund_cluster_id ?? ''));

            if ($reason !== null) {
                $ineligible[] = $this->formatItemLabel($itrItem, $inventoryItem) . ' - ' . $reason;
                continue;
            }

            if ($currentDeptId !== $fromDeptId) {
                $sourceMismatch[] = $this->formatItemLabel($itrItem, $inventoryItem) . ' - Current department no longer matches the ITR source.';
                continue;
            }

            if ($fromOfficer !== '' && $currentOfficer !== $fromOfficer) {
                $sourceMismatch[] = $this->formatItemLabel($itrItem, $inventoryItem) . ' - Current accountable officer no longer matches the ITR source.';
                continue;
            }

            if ($currentClusterId === '' || $currentClusterId !== $fromClusterId) {
                $sourceMismatch[] = $this->formatItemLabel($itrItem, $inventoryItem) . ' - Fund Cluster no longer matches the ITR source.';
            }
        }

        if (!empty($sourceMismatch)) {
            $errors['source_state_mismatch'] = $sourceMismatch;
        }

        if (!empty($ineligible)) {
            $errors['ineligible_items'] = $ineligible;
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function buildOfficeSnapshot(?Department $department): ?string
    {
        if (!$department) {
            return null;
        }

        $abbr = trim((string) ($department->short_name ?? ''));
        if ($abbr !== '') {
            return $abbr;
        }

        $code = trim((string) ($department->code ?? ''));
        if ($code !== '') {
            return $code;
        }

        return $this->nullableTrim((string) ($department->name ?? ''));
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

    private function getIssuedIneligibilityReason(InventoryItem $item): ?string
    {
        if ($item->trashed()) {
            return 'Item is archived.';
        }

        if ((bool) ($item->is_ics ?? false) !== true) {
            return 'Item is not tagged as ICS/semi-expendable.';
        }

        if (trim((string) ($item->department_id ?? '')) === '') {
            return 'Item has no assigned department.';
        }

        if (trim((string) ($item->fund_source_id ?? '')) === '') {
            return 'Item has no assigned fund source.';
        }

        if ((string) ($item->custody_state ?? '') !== InventoryCustodyStates::ISSUED) {
            return 'Item is not currently issued.';
        }

        return null;
    }

    private function formatItemLabel(ItrItem $itrItem, ?InventoryItem $inventoryItem): string
    {
        $inventoryNo = trim((string) ($itrItem->inventory_item_no_snapshot ?? $inventoryItem?->property_number ?? $inventoryItem?->stock_number ?? '-'));
        $itemName = trim((string) ($itrItem->item_name_snapshot ?? $inventoryItem?->item?->item_name ?? '-'));

        return trim($inventoryNo . ' - ' . $itemName);
    }

    private function nullableTrim(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }
}




