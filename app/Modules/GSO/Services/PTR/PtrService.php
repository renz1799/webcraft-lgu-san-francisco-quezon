<?php

namespace App\Modules\GSO\Services\PTR;

use App\Core\Models\Department;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\Ptr;
use App\Modules\GSO\Repositories\Contracts\PTR\PtrRepositoryInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Services\Contracts\PTR\PtrServiceInterface;
use Illuminate\Support\Facades\DB;

class PtrService implements PtrServiceInterface
{
    public function __construct(
        private readonly PtrRepositoryInterface $ptrs,
        private readonly AuditLogServiceInterface $auditLogs,
    ) {}

    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        return $this->ptrs->datatable($filters, $page, $size);
    }

    public function getEditData(string $ptrId): array
    {
        $ptr = $this->ptrs->findOrFail($ptrId)
            ->load([
                'fromDepartment',
                'toDepartment',
                'fromFundSource.fundCluster',
                'toFundSource.fundCluster',
            ])
            ->loadCount('items');

        return [
            'ptr' => $ptr,
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

    public function createDraft(string $actorUserId): Ptr
    {
        return DB::transaction(function () use ($actorUserId) {
            $ptr = $this->ptrs->create([
                'ptr_number' => null,
                'status' => 'draft',
                'transfer_date' => now()->toDateString(),
            ]);

            return $ptr;
        });
    }

    public function update(string $actorUserId, string $ptrId, array $payload): Ptr
    {
        return DB::transaction(function () use ($actorUserId, $ptrId, $payload) {
            $ptr = $this->ptrs->findOrFail($ptrId);
            abort_if((string) $ptr->status !== 'draft', 409, 'Only draft PTR can be edited.');

            $transferType = $payload['transfer_type'] ?? null;
            if ($transferType !== 'others') {
                $payload['transfer_type_other'] = null;
            }

            $updated = $this->ptrs->update($ptr, [
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

    public function delete(string $actorUserId, string $ptrId): Ptr
    {
        return DB::transaction(function () use ($actorUserId, $ptrId) {
            $ptr = $this->ptrs->findOrFail($ptrId);
            $old = $ptr->toArray();

            $this->ptrs->softDelete($ptr);
            $ptr = $this->ptrs->findWithTrashedOrFail($ptrId);

            $this->auditLogs->record(
                'ptr.deleted',
                $ptr,
                $old,
                $ptr->toArray(),
                [
                    'deleted_by_user_id' => $actorUserId,
                ],
                'PTR archived: ' . $this->ptrLabel($old),
                $this->buildPtrDeletedDisplay($old)
            );

            return $ptr;
        });
    }

    public function restore(string $actorUserId, string $ptrId): Ptr
    {
        return DB::transaction(function () use ($actorUserId, $ptrId) {
            $ptr = $this->ptrs->findWithTrashedOrFail($ptrId);
            abort_if($ptr->deleted_at === null, 409, 'PTR is not archived.');

            $old = $ptr->toArray();

            $this->ptrs->restore($ptr);
            $ptr = $this->ptrs->findOrFail($ptrId);

            $this->auditLogs->record(
                'ptr.restored',
                $ptr,
                $old,
                $ptr->toArray(),
                [
                    'restored_by_user_id' => $actorUserId,
                ],
                'PTR restored: ' . $this->ptrLabel($ptr->toArray()),
                $this->buildPtrRestoredDisplay($ptr->toArray())
            );

            return $ptr;
        });
    }

    private function buildPtrDeletedDisplay(array $values): array
    {
        return [
            'summary' => 'PTR archived: ' . $this->ptrLabel($values),
            'subject_label' => $this->ptrLabel($values),
            'sections' => [
                [
                    'title' => 'PTR Lifecycle',
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

    private function buildPtrRestoredDisplay(array $values): array
    {
        return [
            'summary' => 'PTR restored: ' . $this->ptrLabel($values),
            'subject_label' => $this->ptrLabel($values),
            'sections' => [
                [
                    'title' => 'PTR Lifecycle',
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

    private function statusLabel(?string $value): string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return 'None';
        }

        return ucfirst(str_replace('_', ' ', $value));
    }
}

