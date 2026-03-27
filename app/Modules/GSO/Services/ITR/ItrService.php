<?php

namespace App\Modules\GSO\Services\ITR;

use App\Core\Models\Department;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\Itr;
use App\Modules\GSO\Repositories\Contracts\ITR\ItrRepositoryInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Services\Contracts\ITR\ItrServiceInterface;
use Illuminate\Support\Facades\DB;

class ItrService implements ItrServiceInterface
{
    public function __construct(
        private readonly ItrRepositoryInterface $itrs,
        private readonly AuditLogServiceInterface $auditLogs,
    ) {}

    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        return $this->itrs->datatable($filters, $page, $size);
    }

    public function getEditData(string $itrId): array
    {
        $itr = $this->itrs->findOrFail($itrId)
            ->load([
                'fromDepartment',
                'toDepartment',
                'fromFundSource.fundCluster',
                'toFundSource.fundCluster',
            ])
            ->loadCount('items');

        return [
            'itr' => $itr,
            'departments' => Department::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'fundSources' => FundSource::query()
                ->with('fundCluster')
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'fund_cluster_id']),
        ];
    }

    public function createDraft(string $actorUserId): Itr
    {
        return DB::transaction(function () use ($actorUserId) {
            $itr = $this->itrs->create([
                'itr_number' => null,
                'status' => 'draft',
                'transfer_date' => now()->toDateString(),
            ]);

            return $itr;
        });
    }

    public function update(string $actorUserId, string $itrId, array $payload): Itr
    {
        return DB::transaction(function () use ($actorUserId, $itrId, $payload) {
            $itr = $this->itrs->findOrFail($itrId);
            abort_if((string) $itr->status !== 'draft', 409, 'Only draft ITR can be edited.');

            $transferType = $payload['transfer_type'] ?? null;
            if ($transferType !== 'others') {
                $payload['transfer_type_other'] = null;
            }

            $updated = $this->itrs->update($itr, [
                'transfer_date' => $payload['transfer_date'] ?? null,
                'from_department_id' => $payload['from_department_id'] ?? null,
                'from_accountable_officer' => $payload['from_accountable_officer'] ?? null,
                'from_fund_source_id' => $payload['from_fund_source_id'] ?? null,
                'to_department_id' => $payload['to_department_id'] ?? null,
                'to_accountable_officer' => $payload['to_accountable_officer'] ?? null,
                'to_fund_source_id' => $payload['to_fund_source_id'] ?? null,
                'transfer_type' => $transferType,
                'transfer_type_other' => $payload['transfer_type_other'] ?? null,
                'reason_for_transfer' => $payload['reason_for_transfer'] ?? null,
                'approved_by_name' => $payload['approved_by_name'] ?? null,
                'approved_by_designation' => $payload['approved_by_designation'] ?? null,
                'approved_by_date' => $payload['approved_by_date'] ?? null,
                'released_by_name' => $payload['released_by_name'] ?? null,
                'released_by_designation' => $payload['released_by_designation'] ?? null,
                'released_by_date' => $payload['released_by_date'] ?? null,
                'received_by_name' => $payload['received_by_name'] ?? null,
                'received_by_designation' => $payload['received_by_designation'] ?? null,
                'received_by_date' => $payload['received_by_date'] ?? null,
                'remarks' => $payload['remarks'] ?? null,
            ]);

            return $updated;
        });
    }

    public function delete(string $actorUserId, string $itrId): Itr
    {
        return DB::transaction(function () use ($actorUserId, $itrId) {
            $itr = $this->itrs->findOrFail($itrId);
            $old = $itr->toArray();

            $this->itrs->softDelete($itr);
            $itr = $this->itrs->findWithTrashedOrFail($itrId);

            $this->auditLogs->record(
                'itr.deleted',
                $itr,
                $old,
                $itr->toArray(),
                [
                    'deleted_by_user_id' => $actorUserId,
                ],
                'ITR archived: ' . $this->itrLabel($old),
                $this->buildItrDeletedDisplay($old)
            );

            return $itr;
        });
    }

    public function restore(string $actorUserId, string $itrId): Itr
    {
        return DB::transaction(function () use ($actorUserId, $itrId) {
            $itr = $this->itrs->findWithTrashedOrFail($itrId);
            abort_if($itr->deleted_at === null, 409, 'ITR is not archived.');

            $old = $itr->toArray();

            $this->itrs->restore($itr);
            $itr = $this->itrs->findOrFail($itrId);

            $this->auditLogs->record(
                'itr.restored',
                $itr,
                $old,
                $itr->toArray(),
                [
                    'restored_by_user_id' => $actorUserId,
                ],
                'ITR restored: ' . $this->itrLabel($itr->toArray()),
                $this->buildItrRestoredDisplay($itr->toArray())
            );

            return $itr;
        });
    }

    private function buildItrDeletedDisplay(array $values): array
    {
        return [
            'summary' => 'ITR archived: ' . $this->itrLabel($values),
            'subject_label' => $this->itrLabel($values),
            'sections' => [
                [
                    'title' => 'ITR Lifecycle',
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
                            'label' => 'From Department',
                            'value' => $this->resolveDepartmentLabel($values['from_department_id'] ?? null),
                        ],
                        [
                            'label' => 'To Department',
                            'value' => $this->resolveDepartmentLabel($values['to_department_id'] ?? null),
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildItrRestoredDisplay(array $values): array
    {
        return [
            'summary' => 'ITR restored: ' . $this->itrLabel($values),
            'subject_label' => $this->itrLabel($values),
            'sections' => [
                [
                    'title' => 'ITR Lifecycle',
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
                            'label' => 'From Department',
                            'value' => $this->resolveDepartmentLabel($values['from_department_id'] ?? null),
                        ],
                        [
                            'label' => 'To Department',
                            'value' => $this->resolveDepartmentLabel($values['to_department_id'] ?? null),
                        ],
                    ],
                ],
            ],
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

    private function statusLabel(?string $value): string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return 'None';
        }

        return ucfirst(str_replace('_', ' ', $value));
    }
}



