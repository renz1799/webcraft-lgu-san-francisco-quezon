<?php

namespace App\Modules\GSO\Services\PAR;

use App\Core\Models\Department;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Models\AccountableOfficer;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Models\ParItem;
use App\Modules\GSO\Repositories\Contracts\PAR\ParRepositoryInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemEventServiceInterface;
use App\Modules\GSO\Services\Contracts\Numbers\ParNumberServiceInterface;
use App\Modules\GSO\Services\Contracts\PAR\ParWorkflowServiceInterface;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryEventTypes;
use App\Modules\GSO\Support\ParStatuses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ParWorkflowService implements ParWorkflowServiceInterface
{
    public function __construct(
        private readonly ParRepositoryInterface $pars,
        private readonly InventoryItemEventServiceInterface $events,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly ParNumberServiceInterface $parNumbers,
    ) {}

    public function submit(string $actorUserId, string $parId): Par
    {
        return DB::transaction(function () use ($actorUserId, $parId) {
            $par = $this->loadWorkflowPar($parId);

            abort_if((string) $par->status !== ParStatuses::DRAFT, 409, 'Only draft PAR can be submitted.');
            abort_if($par->items->count() === 0, 422, 'PAR must have at least one item.');

            $this->assertReadyForWorkflow($par);

            $parClusterId = (string) ($par->fundSource?->fund_cluster_id ?? '');
            abort_if(trim($parClusterId) === '', 422, 'PAR fund source has no fund cluster.');

            foreach ($par->items as $parItem) {
                $item = $parItem->inventoryItem;
                abort_if(!$item, 422, 'PAR contains an invalid inventory item.');

                $itemClusterId = (string) ($item->fundSource?->fund_cluster_id ?? '');
                abort_if(
                    $itemClusterId !== $parClusterId,
                    422,
                    'Fund cluster mismatch detected. All items in this PAR must belong to the same Fund Cluster.'
                );
            }

            $old = $par->toArray();

            $par->status = ParStatuses::SUBMITTED;
            $par->save();

            $this->auditLogs->record(
                'par.submitted',
                $par,
                $old,
                $par->toArray(),
                [],
                'PAR submitted: ' . $this->parLabel($par->toArray()),
                $this->buildWorkflowTransitionDisplay($old, $par->toArray(), 'PAR submitted', 'Workflow Status', $par)
            );

            return $par->refresh();
        });
    }

    public function reopen(string $actorUserId, string $parId): Par
    {
        return DB::transaction(function () use ($actorUserId, $parId) {
            $par = $this->loadWorkflowPar($parId);

            abort_if((string) $par->status !== ParStatuses::SUBMITTED, 409, 'Only submitted PAR can be reopened.');

            $old = $par->toArray();

            $par->status = ParStatuses::DRAFT;
            $par->save();

            $this->auditLogs->record(
                'par.reopened',
                $par,
                $old,
                $par->toArray(),
                [
                    'reopened_by_user_id' => $actorUserId,
                ],
                'PAR reopened to draft: ' . $this->parLabel($par->toArray()),
                $this->buildWorkflowTransitionDisplay($old, $par->toArray(), 'PAR reopened', 'Workflow Status', $par)
            );

            return $par->refresh();
        });
    }

    public function finalize(string $actorUserId, string $parId): Par
    {
        return DB::transaction(function () use ($actorUserId, $parId) {
            $par = $this->loadWorkflowPar($parId);

            abort_if(!in_array((string) $par->status, [ParStatuses::DRAFT, ParStatuses::SUBMITTED], true), 409, 'Only draft/submitted PAR can be finalized.');
            abort_if($par->items->count() === 0, 422, 'PAR must have at least one item.');

            $this->assertReadyForWorkflow($par);

            $parClusterId = (string) ($par->fundSource?->fund_cluster_id ?? '');
            abort_if(trim($parClusterId) === '', 422, 'PAR fund source has no fund cluster.');

            $mismatched = [];

            foreach ($par->items as $pi) {
                $inv = $pi->inventoryItem;

                if (!$inv) {
                    $mismatched[] = [
                        'property_number' => (string) ($pi->property_number_snapshot ?? '-'),
                        'item_name' => (string) ($pi->item_name_snapshot ?? '-'),
                        'reason' => 'Missing inventory item record.',
                    ];
                    continue;
                }

                $itemClusterId = (string) ($inv->fundSource?->fund_cluster_id ?? '');
                if (trim($itemClusterId) === '') {
                    $mismatched[] = [
                        'property_number' => (string) ($pi->property_number_snapshot ?? $inv->property_number ?? '-'),
                        'item_name' => (string) ($pi->item_name_snapshot ?? $inv->item?->item_name ?? '-'),
                        'reason' => 'Item fund source has no fund cluster.',
                    ];
                    continue;
                }

                if ($itemClusterId !== $parClusterId) {
                    $mismatched[] = [
                        'property_number' => (string) ($pi->property_number_snapshot ?? $inv->property_number ?? '-'),
                        'item_name' => (string) ($pi->item_name_snapshot ?? $inv->item?->item_name ?? '-'),
                        'reason' => 'Fund cluster mismatch.',
                    ];
                }
            }

            if (!empty($mismatched)) {
                $lines = array_map(
                    fn ($x) => trim(($x['property_number'] ?? '-') . ' - ' . ($x['item_name'] ?? '-')),
                    $mismatched
                );

                throw ValidationException::withMessages([
                    'fund_cluster_mismatch' => $lines,
                ])->status(422);
            }

            $notInPool = [];

            foreach ($par->items as $pi) {
                /** @var InventoryItem|null $item */
                $item = $pi->inventoryItem;

                if (!$item) {
                    $notInPool[] = [
                        'inventory_item_id' => (string) ($pi->inventory_item_id ?? ''),
                        'property_number' => (string) ($pi->property_number_snapshot ?? '-'),
                        'item_name' => (string) ($pi->item_name_snapshot ?? '-'),
                        'reason' => 'Missing inventory item record.',
                    ];
                    continue;
                }

                $reason = $this->getPoolIneligibilityReason($item);
                if ($reason !== null) {
                    $notInPool[] = [
                        'inventory_item_id' => (string) $item->id,
                        'property_number' => (string) ($pi->property_number_snapshot ?? $item->property_number ?? '-'),
                        'item_name' => (string) ($pi->item_name_snapshot ?? $item->item?->item_name ?? '-'),
                        'reason' => $reason,
                    ];
                }
            }

            if (!empty($notInPool)) {
                $lines = array_map(fn ($x) => trim(($x['property_number'] ?? '-') . ' - ' . ($x['item_name'] ?? '-')), $notInPool);

                throw ValidationException::withMessages([
                    'not_in_pool' => $lines,
                ])->status(422);
            }

            $old = $par->toArray();

            if (empty($par->par_number)) {
                $par->par_number = $this->parNumbers->nextNumber($par->issued_date ?? now());
            }

            if (empty($par->issued_date)) {
                $par->issued_date = now()->toDateString();
            }

            $par->save();

            $accountableOfficerId = $this->resolveAccountableOfficerId(
                (string) $par->person_accountable,
                (string) $par->department_id
            );

            foreach ($par->items as $pi) {
                /** @var InventoryItem $item */
                $item = $pi->inventoryItem;

                $qty = (int) ($pi->quantity ?? 1);
                abort_if($qty <= 0, 422, 'Invalid PAR item quantity.');

                $officeSnapshot = $this->buildOfficeSnapshot($par->department);
                $officerSnapshot = trim((string) ($par->person_accountable ?? ''));

                $this->events->create((string) $actorUserId, (string) $item->id, [
                    'event_type' => InventoryEventTypes::ISSUED,
                    'event_date' => $par->issued_date?->toDateString() ?? now()->toDateString(),
                    'quantity' => $qty,
                    'department_id' => (string) $par->department_id,
                    'person_accountable' => (string) $par->person_accountable,
                    'office_snapshot' => $officeSnapshot,
                    'officer_snapshot' => $officerSnapshot,
                    'unit_snapshot' => $pi->unit_snapshot ?? $item->unit,
                    'amount_snapshot' => $pi->amount_snapshot ?? $item->acquisition_cost,
                    'status' => 'serviceable',
                    'condition' => $item->condition,
                    'reference_type' => 'PAR',
                    'reference_no' => (string) $par->par_number,
                    'reference_id' => (string) $par->id,
                    'fund_source_id' => (string) ($par->fund_source_id ?? ''),
                ]);

                $item->department_id = (string) $par->department_id;
                $item->accountable_officer = (string) $par->person_accountable;
                $item->accountable_officer_id = $accountableOfficerId;
                $item->custody_state = InventoryCustodyStates::ISSUED;
                $item->status = 'serviceable';
                $item->save();
            }

            $par->status = ParStatuses::FINALIZED;
            $par->save();

            $this->auditLogs->record(
                'par.finalized',
                $par,
                $old,
                $par->toArray(),
                [],
                'PAR finalized for issuance: ' . $this->parLabel($par->toArray()),
                $this->buildFinalizedDisplay($old, $par->toArray(), $par)
            );

            return $par->refresh();
        });
    }

    public function cancel(string $actorUserId, string $parId, ?string $reason = null): Par
    {
        return DB::transaction(function () use ($actorUserId, $parId, $reason) {
            $par = $this->loadWorkflowPar($parId);

            abort_if((string) $par->status === ParStatuses::FINALIZED, 409, 'Finalized PAR cannot be cancelled.');

            $old = $par->toArray();
            $par->status = ParStatuses::CANCELLED;
            $par->remarks = $reason ?: $par->remarks;
            $par->save();

            $this->auditLogs->record(
                'par.cancelled',
                $par,
                $old,
                $par->toArray(),
                [],
                $reason
                    ? 'PAR cancelled: ' . $this->parLabel($par->toArray()) . '. Reason: ' . trim((string) $reason)
                    : 'PAR cancelled: ' . $this->parLabel($par->toArray()),
                $this->buildCancelledDisplay($old, $par->toArray(), $this->nullableTrim((string) ($reason ?? '')), $par)
            );

            return $par->refresh();
        });
    }

    private function assertReadyForWorkflow(Par $par): void
    {
        $errors = [];

        if (!$par->department_id) {
            $errors['department_id'][] = 'Department is required.';
        }

        if (!$par->fund_source_id) {
            $errors['fund_source_id'][] = 'Fund is required.';
        }

        if ($this->nullableTrim((string) ($par->person_accountable ?? '')) === null) {
            $errors['person_accountable'][] = 'Printed Name (End User) is required.';
        }

        if ($this->nullableTrim((string) ($par->received_by_position ?? '')) === null) {
            $errors['received_by_position'][] = 'Position / Office is required.';
        }

        if (!$par->received_by_date) {
            $errors['received_by_date'][] = 'Received By date is required.';
        }

        if ($this->nullableTrim((string) ($par->issued_by_name ?? '')) === null) {
            $errors['issued_by_name'][] = 'Issued By printed name is required.';
        }

        if ($this->nullableTrim((string) ($par->issued_by_position ?? '')) === null) {
            $errors['issued_by_position'][] = 'Issued By position is required.';
        }

        if ($this->nullableTrim((string) ($par->issued_by_office ?? '')) === null) {
            $errors['issued_by_office'][] = 'Issued By office is required.';
        }

        if (!$par->issued_by_date) {
            $errors['issued_by_date'][] = 'Issued By date is required.';
        }

        if (!$par->issued_date) {
            $errors['issued_date'][] = 'Issued Date is required.';
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors)->status(422);
        }
    }

    private function nullableTrim(string $value): ?string
    {
        $value = trim($value);
        return $value === '' ? null : $value;
    }

    private function loadWorkflowPar(string $parId): Par
    {
        /** @var Par $par */
        $par = Par::query()
            ->lockForUpdate()
            ->with([
                'items.inventoryItem.fundSource',
                'items.inventoryItem.item',
                'items.inventoryItem.latestEvent',
                'fundSource',
                'department',
            ])
            ->findOrFail($parId);

        return $par;
    }

    private function buildWorkflowTransitionDisplay(
        array $before,
        array $after,
        string $summaryPrefix,
        string $sectionTitle,
        ?Par $par = null
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
                        'label' => 'PAR Number',
                        'before' => $this->displayValue($before['par_number'] ?? null),
                        'after' => $this->displayValue($after['par_number'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $par?->items?->count()),
        ];

        if ($par && $par->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($par, 'Included Items');
        }

        return [
            'summary' => $summaryPrefix . ': ' . $this->parLabel($after),
            'subject_label' => $this->parLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildFinalizedDisplay(array $before, array $after, Par $par): array
    {
        $itemsCount = $par->items->count();

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
                        'label' => 'PAR Number',
                        'before' => $this->displayValue($before['par_number'] ?? null),
                        'after' => $this->displayValue($after['par_number'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $itemsCount),
            $this->buildIncludedItemsSection($par, 'Issued Items'),
        ];

        return [
            'summary' => 'PAR finalized for issuance: ' . $this->parLabel($after),
            'subject_label' => $this->parLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildCancelledDisplay(array $before, array $after, ?string $reason, ?Par $par = null): array
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
                        'label' => 'PAR Number',
                        'before' => $this->displayValue($before['par_number'] ?? null),
                        'after' => $this->displayValue($after['par_number'] ?? null),
                    ],
                ],
            ],
            $this->buildDocumentContextSection($after, $par?->items?->count()),
        ];

        if ($par && $par->items->isNotEmpty()) {
            $sections[] = $this->buildIncludedItemsSection($par, 'Included Items');
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
            'summary' => 'PAR cancelled: ' . $this->parLabel($after),
            'subject_label' => $this->parLabel($after),
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
                'label' => 'Person Accountable',
                'value' => $this->displayValue($values['person_accountable'] ?? null),
            ],
            [
                'label' => 'Issued By',
                'value' => $this->personSummary(
                    $values['issued_by_name'] ?? null,
                    $values['issued_by_position'] ?? null,
                    $values['issued_by_office'] ?? null
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

    private function buildIncludedItemsSection(Par $par, string $title): array
    {
        $items = $par->items
            ->take(10)
            ->map(function (ParItem $item) {
                $parts = [
                    'Qty: ' . max(1, (int) ($item->quantity ?? 1)),
                ];

                $unit = trim((string) ($item->unit_snapshot ?? ''));
                if ($unit !== '') {
                    $parts[] = 'Unit: ' . $unit;
                }

                $amount = $item->amount_snapshot;
                if ($amount !== null && $amount !== '') {
                    $parts[] = 'Amount: ' . number_format((float) $amount, 2);
                }

                return [
                    'label' => $this->parItemLabel($item),
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

    private function parLabel(array $values): string
    {
        $number = trim((string) ($values['par_number'] ?? ''));
        if ($number !== '') {
            return $number;
        }

        $status = $this->statusLabel($values['status'] ?? null);
        if ($status !== 'None') {
            return sprintf('PAR (%s)', $status);
        }

        return 'PAR';
    }

    private function parItemLabel(ParItem $item): string
    {
        $propertyNumber = trim((string) ($item->property_number_snapshot ?? ''));
        $itemName = trim((string) ($item->item_name_snapshot ?? ''));

        if ($propertyNumber !== '' && $itemName !== '') {
            return $propertyNumber . ' - ' . $itemName;
        }

        if ($propertyNumber !== '') {
            return $propertyNumber;
        }

        if ($itemName !== '') {
            return $itemName;
        }

        return 'PAR Item';
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

    private function personSummary(?string $name, ?string $position, ?string $office): string
    {
        $parts = array_values(array_filter([
            $this->nullableTrim((string) ($name ?? '')),
            $this->nullableTrim((string) ($position ?? '')),
            $this->nullableTrim((string) ($office ?? '')),
        ]));

        return empty($parts) ? 'None' : implode(' | ', $parts);
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

    private function buildOfficeSnapshot(?Department $dept): string
    {
        if (!$dept) {
            return '';
        }

        $shortName = trim((string) ($dept->short_name ?? ''));
        if ($shortName !== '') {
            return $shortName;
        }

        $code = trim((string) ($dept->code ?? ''));
        if ($code !== '') {
            return $code;
        }

        return trim((string) ($dept->name ?? ''));
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
}
