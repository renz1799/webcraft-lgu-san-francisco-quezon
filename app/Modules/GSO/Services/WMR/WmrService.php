<?php

namespace App\Modules\GSO\Services\WMR;

use App\Modules\GSO\Models\FundCluster;
use App\Modules\GSO\Models\Wmr;
use App\Modules\GSO\Repositories\Contracts\WMR\WmrRepositoryInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Services\Contracts\WMR\WmrServiceInterface;
use Illuminate\Support\Facades\DB;

class WmrService implements WmrServiceInterface
{
    public function __construct(
        private readonly WmrRepositoryInterface $wmrs,
        private readonly AuditLogServiceInterface $auditLogs,
    ) {}

    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        return $this->wmrs->datatable($filters, $page, $size);
    }

    public function getEditData(string $wmrId): array
    {
        $wmr = $this->wmrs->findOrFail($wmrId)
            ->load('fundCluster')
            ->loadCount('items');

        return [
            'wmr' => $wmr,
            'fundClusters' => FundCluster::query()->orderBy('code')->get(),
        ];
    }

    public function createDraft(string $actorUserId): Wmr
    {
        return DB::transaction(function () {
            return $this->wmrs->create([
                'wmr_number' => null,
                'status' => 'draft',
                'report_date' => now()->toDateString(),
            ]);
        });
    }

    public function update(string $actorUserId, string $wmrId, array $payload): Wmr
    {
        return DB::transaction(function () use ($wmrId, $payload) {
            $wmr = $this->wmrs->findOrFail($wmrId);
            abort_if((string) $wmr->status !== 'draft', 409, 'Only draft WMR can be edited.');

            $currentFundClusterId = (string) ($wmr->fund_cluster_id ?? '');
            $nextFundClusterId = (string) ($payload['fund_cluster_id'] ?? '');
            abort_if(
                $wmr->items()->count() > 0 && $currentFundClusterId !== $nextFundClusterId,
                409,
                'Remove all disposal items first before changing the WMR fund cluster.'
            );

            return $this->wmrs->update($wmr, [
                'fund_cluster_id' => $payload['fund_cluster_id'] ?? null,
                'place_of_storage' => $payload['place_of_storage'] ?? null,
                'report_date' => $payload['report_date'] ?? null,
                'custodian_name' => $payload['custodian_name'] ?? null,
                'custodian_designation' => $payload['custodian_designation'] ?? null,
                'custodian_date' => $payload['custodian_date'] ?? null,
                'approved_by_name' => $payload['approved_by_name'] ?? null,
                'approved_by_designation' => $payload['approved_by_designation'] ?? null,
                'approved_by_date' => $payload['approved_by_date'] ?? null,
                'inspection_officer_name' => $payload['inspection_officer_name'] ?? null,
                'inspection_officer_designation' => $payload['inspection_officer_designation'] ?? null,
                'inspection_officer_date' => $payload['inspection_officer_date'] ?? null,
                'witness_name' => $payload['witness_name'] ?? null,
                'witness_designation' => $payload['witness_designation'] ?? null,
                'witness_date' => $payload['witness_date'] ?? null,
                'remarks' => $payload['remarks'] ?? null,
            ]);
        });
    }

    public function delete(string $actorUserId, string $wmrId): Wmr
    {
        return DB::transaction(function () use ($actorUserId, $wmrId) {
            $wmr = $this->wmrs->findOrFail($wmrId);
            $old = $wmr->toArray();

            $this->wmrs->softDelete($wmr);
            $wmr = $this->wmrs->findWithTrashedOrFail($wmrId);

            $this->auditLogs->record(
                action: 'wmr.deleted',
                subject: $wmr,
                changesOld: $old,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: [
                    'deleted_by_user_id' => $actorUserId,
                ],
                message: 'WMR archived: ' . $this->wmrLabel($old),
                display: $this->buildWmrDeletedDisplay($old),
            );

            return $wmr;
        });
    }

    public function restore(string $actorUserId, string $wmrId): Wmr
    {
        return DB::transaction(function () use ($actorUserId, $wmrId) {
            $wmr = $this->wmrs->findWithTrashedOrFail($wmrId);
            abort_if($wmr->deleted_at === null, 409, 'WMR is not archived.');

            $old = $wmr->toArray();
            $oldDeletedAt = $wmr->deleted_at?->toDateTimeString();

            $this->wmrs->restore($wmr);
            $wmr = $this->wmrs->findOrFail($wmrId);

            $this->auditLogs->record(
                action: 'wmr.restored',
                subject: $wmr,
                changesOld: ['deleted_at' => $oldDeletedAt],
                changesNew: ['restored_at' => now()->toDateTimeString()],
                meta: [
                    'restored_by_user_id' => $actorUserId,
                ],
                message: 'WMR restored: ' . $this->wmrLabel($wmr->toArray()),
                display: $this->buildWmrRestoredDisplay($wmr->toArray()),
            );

            return $wmr;
        });
    }

    private function buildWmrDeletedDisplay(array $values): array
    {
        return [
            'summary' => 'WMR archived: ' . $this->wmrLabel($values),
            'subject_label' => $this->wmrLabel($values),
            'sections' => [
                [
                    'title' => 'WMR Lifecycle',
                    'items' => [
                        ['label' => 'Archive Status', 'before' => 'Active Record', 'after' => 'Archived'],
                    ],
                ],
            ],
        ];
    }

    private function buildWmrRestoredDisplay(array $values): array
    {
        return [
            'summary' => 'WMR restored: ' . $this->wmrLabel($values),
            'subject_label' => $this->wmrLabel($values),
            'sections' => [
                [
                    'title' => 'WMR Lifecycle',
                    'items' => [
                        ['label' => 'Archive Status', 'before' => 'Archived', 'after' => 'Active Record'],
                    ],
                ],
            ],
        ];
    }

    private function wmrLabel(array $values): string
    {
        $wmrNumber = trim((string) ($values['wmr_number'] ?? ''));
        $status = $this->statusLabel($values['status'] ?? null);

        if ($wmrNumber !== '') {
            return $wmrNumber;
        }

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

        $cluster = FundCluster::query()->find($fundClusterId);

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

    private function statusLabel(?string $status): string
    {
        $status = trim((string) ($status ?? ''));

        if ($status === '') {
            return 'None';
        }

        return ucfirst(str_replace('_', ' ', $status));
    }

    private function displayValue(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : 'None';
    }
}


