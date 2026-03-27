<?php

namespace App\Modules\GSO\Services\PTR;

use App\Core\Models\Department;
use App\Modules\GSO\Models\AccountableOfficer;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\Ptr;
use App\Modules\GSO\Models\PtrItem;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemEventServiceInterface;
use App\Modules\GSO\Services\Contracts\Numbers\PtrNumberServiceInterface;
use App\Modules\GSO\Services\Contracts\PTR\PtrWorkflowServiceInterface;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryEventTypes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PtrWorkflowService implements PtrWorkflowServiceInterface
{
    public function __construct(
        private readonly InventoryItemEventServiceInterface $events,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly PtrNumberServiceInterface $ptrNumbers,
    ) {}

    public function submit(string $actorUserId, string $ptrId): Ptr
    {
        return DB::transaction(function () use ($ptrId) {
            $ptr = $this->loadWorkflowPtr($ptrId);

            abort_if((string) $ptr->status !== 'draft', 409, 'Only draft PTR can be submitted.');

            $this->assertReadyForWorkflow($ptr);
            $this->assertItemsStillTransferable($ptr);

            $old = $ptr->toArray();
            $ptr->status = 'submitted';
            $ptr->save();

            $this->auditLogs->record(
                'ptr.submitted',
                $ptr,
                $old,
                $ptr->toArray(),
                [],
                'PTR submitted: ' . $this->ptrLabel($ptr->toArray()),
                $this->buildWorkflowTransitionDisplay($old, $ptr->toArray(), 'PTR submitted', 'Workflow Status', $ptr)
            );

            return $ptr->refresh();
        });
    }

    public function reopen(string $actorUserId, string $ptrId): Ptr
    {
        return DB::transaction(function () use ($actorUserId, $ptrId) {
            $ptr = $this->loadWorkflowPtr($ptrId);

            abort_if((string) $ptr->status !== 'submitted', 409, 'Only submitted PTR can be reopened.');

            $old = $ptr->toArray();
            $ptr->status = 'draft';
            $ptr->save();

            $this->auditLogs->record(
                'ptr.reopened',
                $ptr,
                $old,
                $ptr->toArray(),
                [
                    'reopened_by_user_id' => $actorUserId,
                ],
                'PTR reopened to draft: ' . $this->ptrLabel($ptr->toArray()),
                $this->buildWorkflowTransitionDisplay($old, $ptr->toArray(), 'PTR reopened', 'Workflow Status', $ptr)
            );

            return $ptr->refresh();
        });
    }

    public function finalize(string $actorUserId, string $ptrId): Ptr
    {
        return DB::transaction(function () use ($actorUserId, $ptrId) {
            /** @var Ptr $ptr */
            $ptr = $this->loadWorkflowPtr($ptrId);

            abort_if(!in_array((string) $ptr->status, ['draft', 'submitted'], true), 409, 'Only draft or submitted PTR can be finalized.');

            $this->assertReadyForWorkflow($ptr);
            $this->assertItemsStillTransferable($ptr);

            $old = $ptr->toArray();

            if (empty($ptr->transfer_date)) {
                $ptr->transfer_date = now()->toDateString();
            }

            if (empty($ptr->ptr_number)) {
                $ptr->ptr_number = $this->ptrNumbers->nextNumber($ptr->transfer_date ?? now());
            }

            $entityName = trim((string) config('print.entity_name', ''));
            if ($entityName === '') {
                $entityName = trim((string) config('app.name', ''));
            }

            $fromClusterCode = $this->nullableTrim((string) ($ptr->fromFundSource?->fundCluster?->code ?? ''));
            $toClusterCode = $this->nullableTrim((string) ($ptr->toFundSource?->fundCluster?->code ?? ''));

            $ptr->entity_name_snapshot = $entityName !== '' ? $entityName : ($ptr->entity_name_snapshot ?: null);
            $ptr->header_fund_cluster_code_snapshot = $fromClusterCode ?? $toClusterCode;
            $ptr->from_department_snapshot = $this->buildOfficeSnapshot($ptr->fromDepartment);
            $ptr->from_fund_cluster_code_snapshot = $fromClusterCode;
            $ptr->to_department_snapshot = $this->buildOfficeSnapshot($ptr->toDepartment);
            $ptr->to_fund_cluster_code_snapshot = $toClusterCode;
            $ptr->save();

            $toAccountableOfficerId = $this->resolveAccountableOfficerId(
                (string) ($ptr->to_accountable_officer ?? ''),
                (string) ($ptr->to_department_id ?? ''),
            );

            foreach ($ptr->items as $ptrItem) {
                /** @var InventoryItem|null $inventoryItem */
                $inventoryItem = $ptrItem->inventoryItem;
                abort_if(!$inventoryItem, 422, 'PTR contains an invalid inventory item.');

                $quantity = max(1, (int) ($inventoryItem->quantity ?? 1));
                $eventDate = $ptr->transfer_date?->toDateString() ?? now()->toDateString();
                $referenceNo = (string) ($ptr->ptr_number ?? '');

                $this->events->create((string) $actorUserId, (string) $inventoryItem->id, [
                    'event_type' => InventoryEventTypes::TRANSFERRED_OUT,
                    'event_date' => $eventDate,
                    'quantity' => $quantity,
                    'department_id' => (string) $ptr->from_department_id,
                    'fund_source_id' => (string) $ptr->from_fund_source_id,
                    'person_accountable' => (string) ($ptr->from_accountable_officer ?? ''),
                    'office_snapshot' => $this->buildOfficeSnapshot($ptr->fromDepartment),
                    'officer_snapshot' => (string) ($ptr->from_accountable_officer ?? ''),
                    'unit_snapshot' => $inventoryItem->unit,
                    'amount_snapshot' => $ptrItem->amount_snapshot ?? $inventoryItem->acquisition_cost,
                    'status' => $inventoryItem->status,
                    'condition' => $inventoryItem->condition,
                    'reference_type' => 'PTR',
                    'reference_no' => $referenceNo,
                    'reference_id' => (string) $ptr->id,
                ]);

                $this->events->create((string) $actorUserId, (string) $inventoryItem->id, [
                    'event_type' => InventoryEventTypes::TRANSFERRED_IN,
                    'event_date' => $eventDate,
                    'quantity' => $quantity,
                    'department_id' => (string) $ptr->to_department_id,
                    'fund_source_id' => (string) $ptr->to_fund_source_id,
                    'person_accountable' => (string) ($ptr->to_accountable_officer ?? ''),
                    'office_snapshot' => $this->buildOfficeSnapshot($ptr->toDepartment),
                    'officer_snapshot' => (string) ($ptr->to_accountable_officer ?? ''),
                    'unit_snapshot' => $inventoryItem->unit,
                    'amount_snapshot' => $ptrItem->amount_snapshot ?? $inventoryItem->acquisition_cost,
                    'status' => $inventoryItem->status,
                    'condition' => $inventoryItem->condition,
                    'reference_type' => 'PTR',
                    'reference_no' => $referenceNo,
                    'reference_id' => (string) $ptr->id,
                ]);

                $inventoryItem->department_id = (string) $ptr->to_department_id;
                $inventoryItem->fund_source_id = (string) $ptr->to_fund_source_id;
                $inventoryItem->accountable_officer = (string) ($ptr->to_accountable_officer ?? '');
                $inventoryItem->accountable_officer_id = $toAccountableOfficerId;
                $inventoryItem->custody_state = InventoryCustodyStates::ISSUED;
                $inventoryItem->save();
            }

            $ptr->status = 'finalized';
            $ptr->save();

            $this->auditLogs->record(
                'ptr.finalized',
                $ptr,
                $old,
                $ptr->toArray(),
                [
                    'items_count' => $ptr->items->count(),
                ],
                'PTR finalized for transfer: ' . $this->ptrLabel($ptr->toArray()),
                $this->buildFinalizedDisplay($old, $ptr->toArray(), $ptr)
            );

            return $ptr->refresh();
        });
    }

    public function cancel(string $actorUserId, string $ptrId, ?string $reason = null): Ptr
    {
        return DB::transaction(function () use ($actorUserId, $ptrId, $reason) {
            $ptr = $this->loadWorkflowPtr($ptrId);

            abort_if(!in_array((string) $ptr->status, ['draft', 'submitted'], true), 409, 'Only draft or submitted PTR can be cancelled.');

            $old = $ptr->toArray();
            $ptr->status = 'cancelled';
            if ($this->nullableTrim((string) ($reason ?? '')) !== null) {
                $ptr->remarks = trim((string) $reason);
            }
            $ptr->save();

            $this->auditLogs->record(
                'ptr.cancelled',
                $ptr,
                $old,
                $ptr->toArray(),
                [
                    'cancelled_by_user_id' => $actorUserId,
                    'reason' => $this->nullableTrim((string) ($reason ?? '')),
                ],
                $reason
                    ? 'PTR cancelled: ' . $this->ptrLabel($ptr->toArray()) . '. Reason: ' . trim((string) $reason)
                    : 'PTR cancelled: ' . $this->ptrLabel($ptr->toArray()),
                $this->buildCancelledDisplay($old, $ptr->toArray(), $this->nullableTrim((string) ($reason ?? '')), $ptr)
            );

            return $ptr->refresh();
        });
    }

    private function loadWorkflowPtr(string $ptrId): Ptr
    {
        return Ptr::query()
            ->lockForUpdate()
            ->with([
                'fromDepartment',
                'toDepartment',
                'fromFundSource.fundCluster',
                'toFundSource.fundCluster',
                'items.inventoryItem.fundSource.fundCluster',
                'items.inventoryItem.item',
            ])
            ->findOrFail($ptrId);
    }

    private function buildWorkflowTransitionDisplay(
        array $before,
        array $after,
        string $summaryPrefix,
        string $sectionTitle,
        ?Ptr $ptr = null
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
                        'label' => 'PTR Number',
                        'before' => $this->displayValue($before['ptr_number'] ?? null),
                        'after' => $this->displayValue($after['ptr_number'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $ptr?->items?->count()),
        ];

        if ($ptr && $ptr->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($ptr, 'Included Items');
        }

        return [
            'summary' => $summaryPrefix . ': ' . $this->ptrLabel($after),
            'subject_label' => $this->ptrLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildFinalizedDisplay(array $before, array $after, Ptr $ptr): array
    {
        $itemsCount = $ptr->items->count();

        return [
            'summary' => 'PTR finalized for transfer: ' . $this->ptrLabel($after),
            'subject_label' => $this->ptrLabel($after),
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
                            'label' => 'PTR Number',
                            'before' => $this->displayValue($before['ptr_number'] ?? null),
                            'after' => $this->displayValue($after['ptr_number'] ?? null),
                        ],
                    ],
                ],
                $this->buildDocumentContextSection($after, $itemsCount),
                $this->buildIncludedItemsSection($ptr, 'Transferred Items'),
            ],
        ];
    }

    private function buildCancelledDisplay(array $before, array $after, ?string $reason, ?Ptr $ptr = null): array
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
                        'label' => 'PTR Number',
                        'before' => $this->displayValue($before['ptr_number'] ?? null),
                        'after' => $this->displayValue($after['ptr_number'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $ptr?->items?->count()),
        ];

        if ($ptr && $ptr->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($ptr, 'Included Items');
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
            'summary' => 'PTR cancelled: ' . $this->ptrLabel($after),
            'subject_label' => $this->ptrLabel($after),
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

    private function buildIncludedItemsSection(Ptr $ptr, string $title): array
    {
        $items = $ptr->items
            ->take(10)
            ->map(function (PtrItem $item) {
                $parts = [];

                $dateAcquired = $item->date_acquired_snapshot?->toDateString();
                if ($dateAcquired) {
                    $parts[] = 'Acquired: ' . $dateAcquired;
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

    private function ptrLabel(array $values): string
    {
        $number = trim((string) ($values['ptr_number'] ?? ''));
        if ($number !== '') {
            return $number;
        }

        $status = $this->statusLabel($values['status'] ?? null);
        if ($status !== 'None') {
            return sprintf('PTR (%s)', $status);
        }

        return 'PTR';
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

    private function assertReadyForWorkflow(Ptr $ptr): void
    {
        $errors = [];

        if (!$ptr->transfer_date) {
            $errors['transfer_date'][] = 'Transfer Date is required.';
        }

        if (!$ptr->from_department_id) {
            $errors['from_department_id'][] = 'From Department is required.';
        }

        if ($this->nullableTrim((string) ($ptr->from_accountable_officer ?? '')) === null) {
            $errors['from_accountable_officer'][] = 'From Accountable Officer is required.';
        }

        if (!$ptr->from_fund_source_id) {
            $errors['from_fund_source_id'][] = 'From Fund Source is required.';
        } elseif ($this->nullableTrim((string) ($ptr->fromFundSource?->fundCluster?->id ?? '')) === null) {
            $errors['from_fund_source_id'][] = 'Selected source Fund Source has no Fund Cluster.';
        }

        if (!$ptr->to_department_id) {
            $errors['to_department_id'][] = 'To Department is required.';
        }

        if ($this->nullableTrim((string) ($ptr->to_accountable_officer ?? '')) === null) {
            $errors['to_accountable_officer'][] = 'To Accountable Officer is required.';
        }

        if (!$ptr->to_fund_source_id) {
            $errors['to_fund_source_id'][] = 'To Fund Source is required.';
        } elseif ($this->nullableTrim((string) ($ptr->toFundSource?->fundCluster?->id ?? '')) === null) {
            $errors['to_fund_source_id'][] = 'Selected destination Fund Source has no Fund Cluster.';
        }

        if ($this->nullableTrim((string) ($ptr->transfer_type ?? '')) === null) {
            $errors['transfer_type'][] = 'Transfer Type is required.';
        }

        if ((string) $ptr->transfer_type === 'others' && $this->nullableTrim((string) ($ptr->transfer_type_other ?? '')) === null) {
            $errors['transfer_type_other'][] = 'Specify the transfer type when Others is selected.';
        }

        if ($this->nullableTrim((string) ($ptr->reason_for_transfer ?? '')) === null) {
            $errors['reason_for_transfer'][] = 'Reason for Transfer is required.';
        }

        if ($this->nullableTrim((string) ($ptr->approved_by_name ?? '')) === null) {
            $errors['approved_by_name'][] = 'Approved By printed name is required.';
        }
        if ($this->nullableTrim((string) ($ptr->approved_by_designation ?? '')) === null) {
            $errors['approved_by_designation'][] = 'Approved By designation is required.';
        }
        if (!$ptr->approved_by_date) {
            $errors['approved_by_date'][] = 'Approved By date is required.';
        }

        if ($this->nullableTrim((string) ($ptr->released_by_name ?? '')) === null) {
            $errors['released_by_name'][] = 'Released / Issued By printed name is required.';
        }
        if ($this->nullableTrim((string) ($ptr->released_by_designation ?? '')) === null) {
            $errors['released_by_designation'][] = 'Released / Issued By designation is required.';
        }
        if (!$ptr->released_by_date) {
            $errors['released_by_date'][] = 'Released / Issued By date is required.';
        }

        if ($this->nullableTrim((string) ($ptr->received_by_name ?? '')) === null) {
            $errors['received_by_name'][] = 'Received By printed name is required.';
        }
        if ($this->nullableTrim((string) ($ptr->received_by_designation ?? '')) === null) {
            $errors['received_by_designation'][] = 'Received By designation is required.';
        }
        if (!$ptr->received_by_date) {
            $errors['received_by_date'][] = 'Received By date is required.';
        }

        if ($ptr->items->count() <= 0) {
            $errors['items'][] = 'Add at least one item before continuing.';
        }

        if (
            (string) $ptr->from_department_id !== ''
            && (string) $ptr->to_department_id !== ''
            && (string) $ptr->from_department_id === (string) $ptr->to_department_id
            && mb_strtolower((string) ($this->nullableTrim((string) ($ptr->from_accountable_officer ?? '')) ?? '')) === mb_strtolower((string) ($this->nullableTrim((string) ($ptr->to_accountable_officer ?? '')) ?? ''))
        ) {
            $errors['to_accountable_officer'][] = 'Destination must differ from the current accountable assignment.';
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function assertItemsStillTransferable(Ptr $ptr): void
    {
        $errors = [];
        $sourceMismatch = [];
        $ineligible = [];

        $fromDeptId = trim((string) ($ptr->from_department_id ?? ''));
        $fromClusterId = trim((string) ($ptr->fromFundSource?->fund_cluster_id ?? ''));
        $fromOfficer = mb_strtolower((string) ($this->nullableTrim((string) ($ptr->from_accountable_officer ?? '')) ?? ''));

        foreach ($ptr->items as $ptrItem) {
            /** @var InventoryItem|null $inventoryItem */
            $inventoryItem = $ptrItem->inventoryItem;

            if (!$inventoryItem) {
                $ineligible[] = $this->formatItemLabel($ptrItem, null) . ' - Missing inventory item record.';
                continue;
            }

            if ((bool) $inventoryItem->is_ics === true) {
                $ineligible[] = $this->formatItemLabel($ptrItem, $inventoryItem) . ' - Item is now tagged as ICS/semi-expendable.';
                continue;
            }

            $reason = $this->getIssuedIneligibilityReason($inventoryItem);
            $currentDeptId = trim((string) ($inventoryItem->department_id ?? ''));
            $currentOfficer = mb_strtolower(trim((string) ($inventoryItem->accountable_officer ?? '')));
            $currentClusterId = trim((string) ($inventoryItem->fundSource?->fund_cluster_id ?? ''));

            if ($reason !== null) {
                $ineligible[] = $this->formatItemLabel($ptrItem, $inventoryItem) . ' - ' . $reason;
                continue;
            }

            if ($currentDeptId !== $fromDeptId) {
                $sourceMismatch[] = $this->formatItemLabel($ptrItem, $inventoryItem) . ' - Current department no longer matches the PTR source.';
                continue;
            }

            if ($fromOfficer !== '' && $currentOfficer !== $fromOfficer) {
                $sourceMismatch[] = $this->formatItemLabel($ptrItem, $inventoryItem) . ' - Current accountable officer no longer matches the PTR source.';
                continue;
            }

            if ($currentClusterId === '' || $currentClusterId !== $fromClusterId) {
                $sourceMismatch[] = $this->formatItemLabel($ptrItem, $inventoryItem) . ' - Fund Cluster no longer matches the PTR source.';
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

        $shortName = trim((string) ($department->short_name ?? ''));
        if ($shortName !== '') {
            return $shortName;
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

        if ((bool) ($item->is_ics ?? false) === true) {
            return 'Item is tagged as ICS/semi-expendable.';
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

    private function formatItemLabel(PtrItem $ptrItem, ?InventoryItem $inventoryItem): string
    {
        $propertyNo = trim((string) ($ptrItem->property_number_snapshot ?? $inventoryItem?->property_number ?? $inventoryItem?->stock_number ?? '-'));
        $itemName = trim((string) ($ptrItem->item_name_snapshot ?? $inventoryItem?->item?->item_name ?? '-'));

        return trim($propertyNo . ' - ' . $itemName);
    }

    private function nullableTrim(string $value): ?string
    {
        $value = trim($value);
        return $value === '' ? null : $value;
    }
}

