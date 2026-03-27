<?php

namespace App\Modules\GSO\Services\WMR;

use App\Core\Models\Department;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\Wmr;
use App\Modules\GSO\Models\WmrItem;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemEventServiceInterface;
use App\Modules\GSO\Services\Contracts\Numbers\WmrNumberServiceInterface;
use App\Modules\GSO\Services\Contracts\WMR\WmrWorkflowServiceInterface;
use App\Modules\GSO\Support\InventoryEventTypes;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WmrWorkflowService implements WmrWorkflowServiceInterface
{
    public function __construct(
        private readonly InventoryItemEventServiceInterface $events,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly WmrNumberServiceInterface $wmrNumbers,
    ) {}

    public function submit(string $actorUserId, string $wmrId): Wmr
    {
        return DB::transaction(function () use ($actorUserId, $wmrId) {
            $wmr = $this->loadWorkflowWmr($wmrId);

            abort_if((string) $wmr->status !== 'draft', 409, 'Only draft WMR can be submitted.');

            $this->assertReadyForSubmission($wmr);
            $this->assertItemsStillDisposable($wmr);

            $old = $wmr->toArray();
            $wmr->status = 'submitted';
            $wmr->save();

            $this->auditLogs->record(
                action: 'wmr.submitted',
                subject: $wmr,
                changesOld: $old,
                changesNew: $wmr->toArray(),
                meta: ['submitted_by_user_id' => $actorUserId],
                message: 'WMR submitted: ' . $this->wmrLabel($wmr->toArray()),
                display: $this->buildWorkflowTransitionDisplay(
                    $old,
                    $wmr->toArray(),
                    'WMR submitted',
                    'Workflow Status',
                    $wmr
                ),
            );

            return $wmr->refresh();
        });
    }

    public function approve(string $actorUserId, string $wmrId): Wmr
    {
        return DB::transaction(function () use ($actorUserId, $wmrId) {
            $wmr = $this->loadWorkflowWmr($wmrId);

            abort_if((string) $wmr->status !== 'submitted', 409, 'Only submitted WMR can be approved.');

            $this->assertReadyForApproval($wmr);
            $this->assertItemsStillDisposable($wmr);

            $old = $wmr->toArray();
            $wmr->status = 'approved';
            $wmr->save();

            $this->auditLogs->record(
                action: 'wmr.approved',
                subject: $wmr,
                changesOld: $old,
                changesNew: $wmr->toArray(),
                meta: ['approved_by_user_id' => $actorUserId],
                message: 'WMR approved for disposal: ' . $this->wmrLabel($wmr->toArray()),
                display: $this->buildWorkflowTransitionDisplay(
                    $old,
                    $wmr->toArray(),
                    'WMR approved',
                    'Workflow Status',
                    $wmr
                ),
            );

            return $wmr->refresh();
        });
    }

    public function reopen(string $actorUserId, string $wmrId): Wmr
    {
        return DB::transaction(function () use ($actorUserId, $wmrId) {
            $wmr = $this->loadWorkflowWmr($wmrId);

            abort_if(!in_array((string) $wmr->status, ['submitted', 'approved'], true), 409, 'Only submitted or approved WMR can be reopened.');

            $old = $wmr->toArray();
            $wmr->status = 'draft';
            $wmr->save();

            $this->auditLogs->record(
                action: 'wmr.reopened',
                subject: $wmr,
                changesOld: $old,
                changesNew: $wmr->toArray(),
                meta: ['reopened_by_user_id' => $actorUserId],
                message: 'WMR reopened to draft: ' . $this->wmrLabel($wmr->toArray()),
                display: $this->buildWorkflowTransitionDisplay(
                    $old,
                    $wmr->toArray(),
                    'WMR reopened',
                    'Workflow Status',
                    $wmr
                ),
            );

            return $wmr->refresh();
        });
    }

    public function finalize(string $actorUserId, string $wmrId): Wmr
    {
        return DB::transaction(function () use ($actorUserId, $wmrId) {
            $wmr = $this->loadWorkflowWmr($wmrId);

            abort_if((string) $wmr->status !== 'approved', 409, 'Only approved WMR can be finalized.');

            $this->assertReadyForFinalize($wmr);
            $this->assertItemsStillDisposable($wmr);

            $old = $wmr->toArray();

            if (!$wmr->report_date) {
                $wmr->report_date = now()->toDateString();
            }

            if (empty($wmr->wmr_number)) {
                $wmr->wmr_number = $this->wmrNumbers->nextNumber($wmr->report_date ?? now());
            }

            $entityName = trim((string) config('print.entity_name', ''));
            if ($entityName === '') {
                $entityName = trim((string) config('app.name', ''));
            }

            $wmr->entity_name_snapshot = $entityName !== '' ? $entityName : ($wmr->entity_name_snapshot ?: null);
            $wmr->fund_cluster_code_snapshot = $this->nullableTrim((string) ($wmr->fundCluster?->code ?? ''));
            $wmr->save();

            foreach ($wmr->items as $wmrItem) {
                $inventoryItem = $wmrItem->inventoryItem;
                abort_if(!$inventoryItem, 422, 'WMR contains an invalid inventory item.');

                $quantity = max(1, (int) ($wmrItem->quantity ?? $inventoryItem->quantity ?? 1));
                $newStatus = (string) $wmrItem->disposal_method === 'transferred_without_cost'
                    ? InventoryStatuses::TRANSFERRED
                    : InventoryStatuses::DISPOSED;

                $this->events->createEvent($inventoryItem, [
                    'event_type' => InventoryEventTypes::DISPOSED,
                    'event_date' => $wmr->report_date?->toDateString() ?? now()->toDateString(),
                    'quantity' => $quantity,
                    'department_id' => (string) ($inventoryItem->department_id ?? ''),
                    'person_accountable' => (string) ($inventoryItem->accountable_officer ?? ''),
                    'office_snapshot' => $this->buildOfficeSnapshot($inventoryItem->department),
                    'officer_snapshot' => (string) ($inventoryItem->accountable_officer ?? ''),
                    'unit_snapshot' => (string) ($wmrItem->unit_snapshot ?? $inventoryItem->unit ?? ''),
                    'amount_snapshot' => $wmrItem->official_receipt_amount ?? $wmrItem->acquisition_cost_snapshot ?? $inventoryItem->acquisition_cost,
                    'status' => $newStatus,
                    'condition' => $wmrItem->condition_snapshot ?? $inventoryItem->condition,
                    'reference_type' => 'WMR',
                    'reference_no' => (string) ($wmr->wmr_number ?? ''),
                    'reference_id' => (string) $wmr->id,
                    'notes' => $this->buildDisposalNotes($wmr, $wmrItem),
                ], $actorUserId);

                $inventoryItem->status = $newStatus;
                if ($this->nullableTrim((string) ($wmrItem->condition_snapshot ?? '')) !== null) {
                    $inventoryItem->condition = (string) $wmrItem->condition_snapshot;
                }
                $inventoryItem->save();
            }

            $wmr->status = 'disposed';
            $wmr->save();

            $this->auditLogs->record(
                action: 'wmr.disposed',
                subject: $wmr,
                changesOld: $old,
                changesNew: $wmr->toArray(),
                meta: [
                    'disposed_by_user_id' => $actorUserId,
                    'items_count' => $wmr->items->count(),
                ],
                message: 'WMR finalized for disposal: ' . $this->wmrLabel($wmr->toArray()),
                display: $this->buildDisposedDisplay($old, $wmr->toArray(), $wmr->items->count(), $wmr),
            );

            return $wmr->refresh();
        });
    }

    public function cancel(string $actorUserId, string $wmrId, ?string $reason = null): Wmr
    {
        return DB::transaction(function () use ($actorUserId, $wmrId, $reason) {
            $wmr = $this->loadWorkflowWmr($wmrId);

            abort_if(!in_array((string) $wmr->status, ['draft', 'submitted', 'approved'], true), 409, 'Only draft, submitted, or approved WMR can be cancelled.');

            $old = $wmr->toArray();
            $wmr->status = 'cancelled';
            if ($this->nullableTrim((string) ($reason ?? '')) !== null) {
                $wmr->remarks = trim((string) $reason);
            }
            $wmr->save();

            $this->auditLogs->record(
                action: 'wmr.cancelled',
                subject: $wmr,
                changesOld: $old,
                changesNew: $wmr->toArray(),
                meta: [
                    'cancelled_by_user_id' => $actorUserId,
                    'reason' => $this->nullableTrim((string) ($reason ?? '')),
                ],
                message: $reason
                    ? 'WMR cancelled: ' . $this->wmrLabel($wmr->toArray()) . '. Reason: ' . trim((string) $reason)
                    : 'WMR cancelled: ' . $this->wmrLabel($wmr->toArray()),
                display: $this->buildCancelledDisplay($old, $wmr->toArray(), $this->nullableTrim((string) ($reason ?? '')), $wmr),
            );

            return $wmr->refresh();
        });
    }

    private function loadWorkflowWmr(string $wmrId): Wmr
    {
        return Wmr::query()
            ->lockForUpdate()
            ->with([
                'fundCluster',
                'items.inventoryItem.department',
                'items.inventoryItem.fundSource.fundCluster',
                'items.inventoryItem.item',
            ])
            ->findOrFail($wmrId);
    }

    private function assertReadyForSubmission(Wmr $wmr): void
    {
        $errors = [];

        if (!$wmr->report_date) {
            $errors['report_date'][] = 'Report Date is required.';
        }

        if (!$wmr->fund_cluster_id) {
            $errors['fund_cluster_id'][] = 'Fund Cluster is required.';
        }

        if ($this->nullableTrim((string) ($wmr->place_of_storage ?? '')) === null) {
            $errors['place_of_storage'][] = 'Place of Storage is required.';
        }

        if ($this->nullableTrim((string) ($wmr->custodian_name ?? '')) === null) {
            $errors['custodian_name'][] = 'Custodian printed name is required.';
        }

        if ($this->nullableTrim((string) ($wmr->custodian_designation ?? '')) === null) {
            $errors['custodian_designation'][] = 'Custodian designation is required.';
        }

        if (!$wmr->custodian_date) {
            $errors['custodian_date'][] = 'Certified Correct date is required.';
        }

        if ($wmr->items->count() <= 0) {
            $errors['items'][] = 'Add at least one disposal item before continuing.';
        }

        foreach ($wmr->items as $wmrItem) {
            $label = $this->formatItemLabel($wmrItem, $wmrItem->inventoryItem);

            if ((int) ($wmrItem->quantity ?? 0) <= 0) {
                $errors['items'][] = $label . ' - Quantity must be greater than zero.';
            }

            $method = $this->nullableTrim((string) ($wmrItem->disposal_method ?? ''));
            if ($method === null) {
                $errors['items'][] = $label . ' - Disposal method is required.';
                continue;
            }

            if ($method === 'transferred_without_cost' && $this->nullableTrim((string) ($wmrItem->transfer_entity_name ?? '')) === null) {
                $errors['items'][] = $label . ' - Receiving agency/entity is required for transfer without cost.';
            }

            if (in_array($method, ['private_sale', 'public_auction'], true)) {
                if ($this->nullableTrim((string) ($wmrItem->official_receipt_no ?? '')) === null) {
                    $errors['items'][] = $label . ' - Official receipt no. is required for sale disposal methods.';
                }
                if (!$wmrItem->official_receipt_date) {
                    $errors['items'][] = $label . ' - Official receipt date is required for sale disposal methods.';
                }
                if ((float) ($wmrItem->official_receipt_amount ?? 0) <= 0) {
                    $errors['items'][] = $label . ' - Official receipt amount must be greater than zero for sale disposal methods.';
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function assertReadyForApproval(Wmr $wmr): void
    {
        $this->assertReadyForSubmission($wmr);

        $errors = [];

        if ($this->nullableTrim((string) ($wmr->approved_by_name ?? '')) === null) {
            $errors['approved_by_name'][] = 'Disposal Approved printed name is required.';
        }
        if ($this->nullableTrim((string) ($wmr->approved_by_designation ?? '')) === null) {
            $errors['approved_by_designation'][] = 'Disposal Approved designation is required.';
        }
        if (!$wmr->approved_by_date) {
            $errors['approved_by_date'][] = 'Disposal Approved date is required.';
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function assertReadyForFinalize(Wmr $wmr): void
    {
        $this->assertReadyForApproval($wmr);

        $errors = [];

        if ($this->nullableTrim((string) ($wmr->inspection_officer_name ?? '')) === null) {
            $errors['inspection_officer_name'][] = 'Inspection Officer printed name is required.';
        }
        if ($this->nullableTrim((string) ($wmr->inspection_officer_designation ?? '')) === null) {
            $errors['inspection_officer_designation'][] = 'Inspection Officer designation is required.';
        }
        if (!$wmr->inspection_officer_date) {
            $errors['inspection_officer_date'][] = 'Inspection Officer date is required.';
        }
        if ($this->nullableTrim((string) ($wmr->witness_name ?? '')) === null) {
            $errors['witness_name'][] = 'Witness printed name is required.';
        }
        if ($this->nullableTrim((string) ($wmr->witness_designation ?? '')) === null) {
            $errors['witness_designation'][] = 'Witness designation is required.';
        }
        if (!$wmr->witness_date) {
            $errors['witness_date'][] = 'Witness date is required.';
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function assertItemsStillDisposable(Wmr $wmr): void
    {
        $errors = [];
        $fundClusterMismatch = [];
        $ineligible = [];
        $wmrClusterId = trim((string) ($wmr->fund_cluster_id ?? ''));

        foreach ($wmr->items as $wmrItem) {
            $inventoryItem = $wmrItem->inventoryItem;
            $label = $this->formatItemLabel($wmrItem, $inventoryItem);

            if (!$inventoryItem) {
                $ineligible[] = $label . ' - Missing inventory item record.';
                continue;
            }

            $currentStatus = trim((string) ($inventoryItem->status ?? ''));
            if (in_array($currentStatus, [
                InventoryStatuses::DISPOSED,
                InventoryStatuses::TRANSFERRED,
                InventoryStatuses::LOST,
                InventoryStatuses::RETURNED,
            ], true)) {
                $ineligible[] = $label . ' - Item is no longer eligible for disposal.';
                continue;
            }

            if ($currentStatus !== '' && !in_array($currentStatus, [
                InventoryStatuses::SERVICEABLE,
                InventoryStatuses::FOR_REPAIR,
                InventoryStatuses::UNDER_REPAIR,
                InventoryStatuses::UNSERVICEABLE,
            ], true)) {
                $ineligible[] = $label . ' - Item status is not valid for this WMR.';
                continue;
            }

            $itemClusterId = trim((string) ($inventoryItem->fundSource?->fund_cluster_id ?? ''));
            if ($wmrClusterId !== '' && ($itemClusterId === '' || $itemClusterId !== $wmrClusterId)) {
                $fundClusterMismatch[] = $label . ' - Fund Cluster no longer matches this WMR.';
            }
        }

        if (!empty($fundClusterMismatch)) {
            $errors['fund_cluster_mismatch'] = $fundClusterMismatch;
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

    private function buildDisposalNotes(Wmr $wmr, WmrItem $wmrItem): ?string
    {
        $parts = [];
        $method = match ((string) ($wmrItem->disposal_method ?? '')) {
            'destroyed' => 'Destroyed',
            'private_sale' => 'Sold at private sale',
            'public_auction' => 'Sold at public auction',
            'transferred_without_cost' => 'Transferred without cost',
            default => null,
        };

        if ($method !== null) {
            $parts[] = $method;
        }

        $transferEntity = $this->nullableTrim((string) ($wmrItem->transfer_entity_name ?? ''));
        if ($transferEntity !== null) {
            $parts[] = 'To: ' . $transferEntity;
        }

        $receiptNo = $this->nullableTrim((string) ($wmrItem->official_receipt_no ?? ''));
        if ($receiptNo !== null) {
            $parts[] = 'OR No.: ' . $receiptNo;
        }

        if ($wmrItem->official_receipt_date) {
            $parts[] = 'OR Date: ' . $wmrItem->official_receipt_date->format('Y-m-d');
        }

        if ($wmrItem->official_receipt_amount !== null) {
            $parts[] = 'Amount: ' . number_format((float) $wmrItem->official_receipt_amount, 2);
        }

        $remarks = $this->nullableTrim((string) ($wmr->remarks ?? ''));
        if ($remarks !== null) {
            $parts[] = 'WMR Notes: ' . $remarks;
        }

        return empty($parts) ? null : implode(' | ', $parts);
    }

    private function formatItemLabel(WmrItem $wmrItem, ?InventoryItem $inventoryItem): string
    {
        $reference = trim((string) ($wmrItem->reference_no_snapshot ?? $inventoryItem?->property_number ?? $inventoryItem?->stock_number ?? '-'));
        $itemName = trim((string) ($wmrItem->item_name_snapshot ?? $inventoryItem?->item?->item_name ?? '-'));

        return trim($reference . ' - ' . $itemName);
    }

    private function nullableTrim(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function buildWorkflowTransitionDisplay(
        array $before,
        array $after,
        string $summaryPrefix,
        string $sectionTitle,
        ?Wmr $wmr = null
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
                        'label' => 'WMR Number',
                        'before' => $this->displayValue($before['wmr_number'] ?? null),
                        'after' => $this->displayValue($after['wmr_number'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $wmr?->items?->count() ?? null),
        ];

        if ($wmr && $wmr->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($wmr, 'Included Items');
        }

        return [
            'summary' => $summaryPrefix . ': ' . $this->wmrLabel($after),
            'subject_label' => $this->wmrLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildDisposedDisplay(array $before, array $after, int $itemsCount, ?Wmr $wmr = null): array
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
                        'label' => 'Items Finalized',
                        'before' => 'None',
                        'after' => (string) $itemsCount,
                    ],
                    [
                        'label' => 'WMR Number',
                        'before' => $this->displayValue($before['wmr_number'] ?? null),
                        'after' => $this->displayValue($after['wmr_number'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $itemsCount),
        ];

        if ($wmr && $wmr->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($wmr, 'Disposed Items');
        }

        return [
            'summary' => 'WMR finalized for disposal: ' . $this->wmrLabel($after),
            'subject_label' => $this->wmrLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildCancelledDisplay(array $before, array $after, ?string $reason, ?Wmr $wmr = null): array
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
                        'label' => 'WMR Number',
                        'before' => $this->displayValue($before['wmr_number'] ?? null),
                        'after' => $this->displayValue($after['wmr_number'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $wmr?->items?->count() ?? null),
        ];

        if ($wmr && $wmr->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($wmr, 'Included Items');
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
            'summary' => 'WMR cancelled: ' . $this->wmrLabel($after),
            'subject_label' => $this->wmrLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildDocumentContextSection(array $values, ?int $itemsCount = null): array
    {
        $items = [
            [
                'label' => 'Fund Cluster',
                'value' => $this->resolveFundClusterLabel($values['fund_cluster_id'] ?? null),
            ],
            [
                'label' => 'Report Date',
                'value' => $this->displayValue($values['report_date'] ?? null),
            ],
            [
                'label' => 'Place of Storage',
                'value' => $this->displayValue($values['place_of_storage'] ?? null),
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

    private function buildIncludedItemsSection(Wmr $wmr, string $title): array
    {
        return [
            'title' => $title,
            'items' => $wmr->items
                ->take(10)
                ->map(function (WmrItem $item) {
                    return [
                        'label' => $this->formatItemLabel($item, $item->inventoryItem),
                        'value' => implode(' | ', array_filter([
                            'Qty: ' . max(1, (int) ($item->quantity ?? 1)),
                            'Unit: ' . $this->displayValue($item->unit_snapshot ?? null),
                            'Method: ' . $this->disposalMethodLabel($item->disposal_method ?? null),
                            $this->nullableTrim((string) ($item->condition_snapshot ?? '')) !== null
                                ? 'Condition: ' . $item->condition_snapshot
                                : null,
                            $this->nullableTrim((string) ($item->transfer_entity_name ?? '')) !== null
                                ? 'Transfer To: ' . $item->transfer_entity_name
                                : null,
                        ])),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    private function wmrLabel(array $values): string
    {
        $wmrNumber = trim((string) ($values['wmr_number'] ?? ''));
        if ($wmrNumber !== '') {
            return $wmrNumber;
        }

        $status = $this->statusLabel($values['status'] ?? null);
        if ($status !== 'None') {
            return sprintf('WMR (%s)', $status);
        }

        return 'WMR';
    }

    private function resolveFundClusterLabel(?string $fundClusterId): string
    {
        $fundClusterId = trim((string) ($fundClusterId ?? ''));

        if ($fundClusterId === '') {
            return 'None';
        }

        $cluster = $this->loadWorkflowFundCluster($fundClusterId);

        if (!$cluster) {
            return 'Unknown Fund Cluster';
        }

        $code = trim((string) ($cluster->code ?? ''));
        $name = trim((string) ($cluster->name ?? ''));

        if ($code !== '' && $name !== '') {
            return sprintf('%s (%s)', $code, $name);
        }

        if ($code !== '') {
            return $code;
        }

        if ($name !== '') {
            return $name;
        }

        return 'Fund Cluster';
    }

    private function loadWorkflowFundCluster(string $fundClusterId): ?\App\Modules\GSO\Models\FundCluster
    {
        return \App\Modules\GSO\Models\FundCluster::query()->find($fundClusterId);
    }

    private function statusLabel(?string $value): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return 'None';
        }

        return ucfirst(str_replace('_', ' ', $value));
    }

    private function disposalMethodLabel(?string $value): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return 'None';
        }

        return match ($value) {
            'destroyed' => 'Destroyed',
            'private_sale' => 'Private Sale',
            'public_auction' => 'Public Auction',
            'transferred_without_cost' => 'Transferred Without Cost',
            default => ucfirst(str_replace('_', ' ', $value)),
        };
    }

    private function displayValue(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : 'None';
    }
}

