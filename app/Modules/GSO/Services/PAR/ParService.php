<?php

namespace App\Modules\GSO\Services\PAR;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Models\ParItem;
use App\Modules\GSO\Repositories\Contracts\PAR\ParItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\PAR\ParRepositoryInterface;
use App\Modules\GSO\Services\Contracts\Numbers\ParNumberServiceInterface;
use App\Modules\GSO\Services\Contracts\PAR\ParServiceInterface;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\ParStatuses;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ParService implements ParServiceInterface
{
    public function __construct(
        private readonly ParRepositoryInterface $pars,
        private readonly ParItemRepositoryInterface $parItems,
        private readonly ParNumberServiceInterface $parNumbers,
        private readonly AuditLogServiceInterface $auditLogs,
    ) {}

    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        return $this->pars->datatable($filters, $page, $size);
    }

    public function createDraft(string $actorUserId, array $payload): Par
    {
        return DB::transaction(function () use ($actorUserId, $payload) {
            $defaultFund = FundSource::query()
                ->with('fundCluster')
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereRaw('LOWER(name) = ?', ['general fund'])
                        ->orWhereRaw('LOWER(code) = ?', ['general fund']);
                })
                ->orderBy('name')
                ->first(['id']);

            $data = [
                'par_number' => null,
                'fund_source_id' => $payload['fund_source_id'] ?? $defaultFund?->id,
                'department_id' => $payload['department_id'] ?? null,
                'person_accountable' => $payload['person_accountable'] ?? null,
                'received_by_position' => $payload['received_by_position'] ?? null,
                'received_by_date' => $payload['received_by_date'] ?? null,
                'issued_by_name' => $payload['issued_by_name'] ?? null,
                'issued_by_position' => $payload['issued_by_position'] ?? null,
                'issued_by_office' => $payload['issued_by_office'] ?? null,
                'issued_by_date' => $payload['issued_by_date'] ?? null,
                'issued_date' => $payload['issued_date'] ?? null,
                'status' => ParStatuses::DRAFT,
                'remarks' => $payload['remarks'] ?? null,
            ];

            $par = $this->pars->create($data);

            return $par;
        });
    }

    public function update(string $actorUserId, string $parId, array $payload): Par
    {
        return DB::transaction(function () use ($parId, $payload) {
            $par = $this->pars->findOrFail($parId);
            abort_if((string) $par->status !== ParStatuses::DRAFT, 409, 'Only draft PAR can be edited.');

            $activeItemsExist = $par->items()->exists();
            $incomingFundSourceId = $payload['fund_source_id'] ?? null;
            $currentFundSourceId = $par->fund_source_id ? (string) $par->fund_source_id : null;

            if ($activeItemsExist && $incomingFundSourceId !== null && (string) $incomingFundSourceId !== (string) $currentFundSourceId) {
                abort(409, 'Remove all PAR items before changing the Fund Source.');
            }

            $updated = $this->pars->update($par, [
                'department_id' => $payload['department_id'] ?? null,
                'fund_source_id' => $payload['fund_source_id'] ?? null,
                'person_accountable' => $payload['person_accountable'] ?? null,
                'received_by_position' => $payload['received_by_position'] ?? null,
                'received_by_date' => $payload['received_by_date'] ?? null,
                'issued_by_name' => $payload['issued_by_name'] ?? null,
                'issued_by_position' => $payload['issued_by_position'] ?? null,
                'issued_by_office' => $payload['issued_by_office'] ?? null,
                'issued_by_date' => $payload['issued_by_date'] ?? null,
                'issued_date' => $payload['issued_date'] ?? null,
                'remarks' => $payload['remarks'] ?? null,
            ]);

            return $updated;
        });
    }

    public function addItem(string $actorUserId, Par $par, InventoryItem $item, int $quantity = 1): void
    {
        DB::transaction(function () use ($actorUserId, $par, $item, $quantity) {

            if ((string) $par->status !== ParStatuses::DRAFT) {
                abort(409, 'Only draft PAR can be modified.');
            }

            // ✅ Load fund sources (so we can compare clusters)
            $par->loadMissing(['fundSource']);
            $item->loadMissing(['fundSource', 'latestEvent', 'item']);

            $parFundSourceId = (string) ($par->fund_source_id ?? '');
            abort_if(trim($parFundSourceId) === '', 409, 'This PAR has no Fund Cluster selected.');

            $itemFundSourceId = (string) ($item->fund_source_id ?? '');
            abort_if(trim($itemFundSourceId) === '', 422, 'This item has no Fund Cluster assigned and cannot be added to a PAR.');

            $parClusterId  = (string) ($par->fundSource?->fund_cluster_id ?? '');
            $itemClusterId = (string) ($item->fundSource?->fund_cluster_id ?? '');

            abort_if(trim($parClusterId) === '', 409, 'This PAR fund source is missing its Fund Cluster.');
            abort_if(trim($itemClusterId) === '', 422, 'This item fund source is missing its Fund Cluster.');

            abort_if($itemClusterId !== $parClusterId, 422, 'Fund Cluster mismatch. Only items under this PAR’s Fund Cluster can be added.');

            $poolDeptId = trim((string) config('gso.pool.department_id', ''));
            $currentDeptId = trim((string) ($item->latestEvent?->department_id ?? $item->department_id ?? ''));
            if ($poolDeptId !== '' && $currentDeptId !== '') {
                abort_if($currentDeptId !== $poolDeptId, 422, 'Only items currently in the GSO pool can be added to a PAR.');
            }
            abort_if((string) ($item->custody_state ?? '') !== InventoryCustodyStates::POOL, 422, 'Only items currently in the GSO pool can be added to a PAR.');

            // Prevent duplicates
            if ($this->parItems->exists($par->id, $item->id)) {
                return;
            }

            $this->parItems->add([
                'par_id' => $par->id,
                'inventory_item_id' => $item->id,
                'quantity' => max(1, $quantity),

                // snapshots (print stability)
                'property_number_snapshot' => $item->property_number,
                'amount_snapshot' => $item->acquisition_cost,
                'unit_snapshot' => $item->unit,
                'item_name_snapshot' => $item->item?->item_name ?? $item->item_name ?? null,
            ]);

        });
    }

    public function removeItem(string $actorUserId, Par $par, ParItem $parItem): void
    {
        DB::transaction(function () use ($actorUserId, $par, $parItem) {

            // lock parent row to prevent race with submit/finalize
            /** @var Par $lockedPar */
            $lockedPar = Par::query()->lockForUpdate()->findOrFail($par->id);

            abort_if((string) $lockedPar->status !== 'draft', 409, 'Only draft PAR can be edited.');
            abort_if($lockedPar->deleted_at !== null, 409, 'Archived PAR cannot be edited.');

            // ensure not already deleted
            if (method_exists($parItem, 'trashed') && $parItem->trashed()) {
                return;
            }

            // soft delete
            $this->parItems->delete($parItem);
        });
    }

        public function getItems(Par $par): Collection
        {
            return $this->parItems->listByParId($par->id);
        }

        public function delete(string $actorUserId, string $parId): Par
    {
        return DB::transaction(function () use ($actorUserId, $parId) {

            $par = $this->pars->findOrFail($parId);

            // snapshot BEFORE delete
            $old = $par->toArray();

            $this->pars->softDelete($par);

            $par = $this->pars->findWithTrashedOrFail($parId);

            $this->auditLogs->record(
                'par.deleted',
                $par,
                $old,
                $par->toArray(),
                [],
                'PAR archived: ' . $this->parLabel($old),
                $this->buildParDeletedDisplay($old)
            );

            return $par;
        });
    }

    public function restore(string $actorUserId, string $parId): Par
    {
        return DB::transaction(function () use ($actorUserId, $parId) {

            $par = $this->pars->findWithTrashedOrFail($parId);

            abort_if($par->deleted_at === null, 409, 'PAR is not archived.');

            $old = $par->toArray();

            $this->pars->restore($par);

            $par = $this->pars->findOrFail($parId);

            $this->auditLogs->record(
                'par.restored',
                $par,
                $old,
                $par->toArray(),
                [],
                'PAR restored: ' . $this->parLabel($par->toArray()),
                $this->buildParRestoredDisplay($par->toArray())
            );

            return $par;
        });
    }

    private function buildParDeletedDisplay(array $values): array
    {
        return [
            'summary' => 'PAR archived: ' . $this->parLabel($values),
            'subject_label' => $this->parLabel($values),
            'sections' => [
                [
                    'title' => 'PAR Lifecycle',
                    'items' => [
                        [
                            'label' => 'Archive Status',
                            'before' => 'Active Record',
                            'after' => 'Archived',
                        ],
                        [
                            'label' => 'Status',
                            'value' => $this->statusLabel($values['status'] ?? null),
                        ],
                        [
                            'label' => 'Department',
                            'value' => $this->resolveDepartmentLabel($values['department_id'] ?? null),
                        ],
                        [
                            'label' => 'Fund Source',
                            'value' => $this->resolveFundSourceLabel($values['fund_source_id'] ?? null),
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildParRestoredDisplay(array $values): array
    {
        return [
            'summary' => 'PAR restored: ' . $this->parLabel($values),
            'subject_label' => $this->parLabel($values),
            'sections' => [
                [
                    'title' => 'PAR Lifecycle',
                    'items' => [
                        [
                            'label' => 'Archive Status',
                            'before' => 'Archived',
                            'after' => 'Active Record',
                        ],
                        [
                            'label' => 'Status',
                            'value' => $this->statusLabel($values['status'] ?? null),
                        ],
                        [
                            'label' => 'Department',
                            'value' => $this->resolveDepartmentLabel($values['department_id'] ?? null),
                        ],
                        [
                            'label' => 'Fund Source',
                            'value' => $this->resolveFundSourceLabel($values['fund_source_id'] ?? null),
                        ],
                    ],
                ],
            ],
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

    private function statusLabel(?string $value): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return 'None';
        }

        return ucfirst(str_replace('_', ' ', $value));
    }
}







