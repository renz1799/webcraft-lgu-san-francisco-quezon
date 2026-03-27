<?php

namespace App\Modules\GSO\Services\ICS;

use App\Core\Models\Department;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\Ics;
use App\Modules\GSO\Repositories\Contracts\ICS\IcsRepositoryInterface;
use App\Modules\GSO\Services\Contracts\ICS\IcsServiceInterface;
use Illuminate\Support\Facades\DB;

class IcsService implements IcsServiceInterface
{
    public function __construct(
        private readonly IcsRepositoryInterface $icsRepo,
        private readonly AuditLogServiceInterface $auditLogs,
    ) {}

    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        return $this->icsRepo->datatable($filters, $page, $size);
    }

    public function getEditData(string $icsId): array
    {
        $ics = $this->icsRepo->findOrFail($icsId)
            ->load(['department', 'fundSource.fundCluster'])
            ->loadCount('items');

        return [
            'ics' => $ics,
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

    public function createDraft(string $actorUserId): Ics
    {
        return DB::transaction(function () {
            $defaultFund = FundSource::query()
                ->with('fundCluster')
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereRaw('LOWER(name) = ?', ['general fund'])
                        ->orWhereRaw('LOWER(code) = ?', ['general fund']);
                })
                ->orderBy('name')
                ->first(['id']);

            return $this->icsRepo->create([
                'ics_number' => null,
                'status' => 'draft',
                'issued_date' => now()->toDateString(),
                'fund_source_id' => $defaultFund?->id,
            ]);
        });
    }

    public function update(string $actorUserId, string $icsId, array $payload): Ics
    {
        return DB::transaction(function () use ($icsId, $payload) {
            $ics = $this->icsRepo->findOrFail($icsId);
            abort_if((string) $ics->status !== 'draft', 409, 'Only draft ICS can be edited.');

            $activeItemsExist = $ics->items()->exists();
            $incomingFundSourceId = $payload['fund_source_id'] ?? null;
            $currentFundSourceId = $ics->fund_source_id ? (string) $ics->fund_source_id : null;

            if ($activeItemsExist && $incomingFundSourceId !== null && (string) $incomingFundSourceId !== (string) $currentFundSourceId) {
                abort(409, 'Remove all ICS items before changing the Fund Source.');
            }

            return $this->icsRepo->update($ics, [
                'department_id' => $payload['department_id'] ?? null,
                'fund_source_id' => $payload['fund_source_id'] ?? null,
                'issued_date' => $payload['issued_date'] ?? null,
                'received_from_name' => $payload['received_from_name'] ?? null,
                'received_from_position' => $payload['received_from_position'] ?? null,
                'received_from_office' => $payload['received_from_office'] ?? null,
                'received_from_date' => $payload['received_from_date'] ?? null,
                'received_by_name' => $payload['received_by_name'] ?? null,
                'received_by_position' => $payload['received_by_position'] ?? null,
                'received_by_office' => $payload['received_by_office'] ?? null,
                'received_by_date' => $payload['received_by_date'] ?? null,
                'remarks' => $payload['remarks'] ?? null,
            ]);
        });
    }

    public function delete(string $actorUserId, string $icsId): Ics
    {
        return DB::transaction(function () use ($icsId) {
            $ics = $this->icsRepo->findOrFail($icsId);
            $old = $ics->toArray();

            $this->icsRepo->softDelete($ics);
            $ics = $this->icsRepo->findWithTrashedOrFail($icsId);

            $this->auditLogs->record(
                action: 'ics.deleted',
                subject: $ics,
                changesOld: $old,
                changesNew: ['deleted_at' => $ics->deleted_at?->toDateTimeString()],
                meta: [],
                message: 'ICS archived: ' . $this->icsLabel($old),
                display: $this->buildIcsDeletedDisplay($old),
            );

            return $ics;
        });
    }

    public function restore(string $actorUserId, string $icsId): Ics
    {
        return DB::transaction(function () use ($icsId) {
            $ics = $this->icsRepo->findWithTrashedOrFail($icsId);
            abort_if($ics->deleted_at === null, 409, 'ICS is not archived.');

            $deletedAt = $ics->deleted_at?->toDateTimeString();
            $old = $ics->toArray();

            $this->icsRepo->restore($ics);
            $ics = $this->icsRepo->findOrFail($icsId);

            $this->auditLogs->record(
                action: 'ics.restored',
                subject: $ics,
                changesOld: ['deleted_at' => $deletedAt],
                changesNew: ['restored_at' => now()->toDateTimeString()],
                meta: [],
                message: 'ICS restored: ' . $this->icsLabel($ics->toArray()),
                display: $this->buildIcsRestoredDisplay($old),
            );

            return $ics;
        });
    }

    private function buildIcsDeletedDisplay(array $values): array
    {
        return [
            'summary' => 'ICS archived: ' . $this->icsLabel($values),
            'subject_label' => $this->icsLabel($values),
            'sections' => [[
                'title' => 'ICS Lifecycle',
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
            ]],
        ];
    }

    private function buildIcsRestoredDisplay(array $values): array
    {
        return [
            'summary' => 'ICS restored: ' . $this->icsLabel($values),
            'subject_label' => $this->icsLabel($values),
            'sections' => [[
                'title' => 'ICS Lifecycle',
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
            ]],
        ];
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

    private function statusLabel(?string $value): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return 'None';
        }

        return ucfirst(str_replace('_', ' ', $value));
    }
}
