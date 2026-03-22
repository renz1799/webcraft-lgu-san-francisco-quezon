<?php

namespace App\Modules\GSO\Services;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\Contracts\FundClusterDatatableRowBuilderInterface;
use App\Modules\GSO\Models\FundCluster;
use App\Modules\GSO\Repositories\Contracts\FundClusterRepositoryInterface;
use App\Modules\GSO\Services\Contracts\FundClusterServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FundClusterService implements FundClusterServiceInterface
{
    public function __construct(
        private readonly FundClusterRepositoryInterface $fundClusters,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly FundClusterDatatableRowBuilderInterface $datatableRowBuilder,
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));
        $paginator = $this->fundClusters->paginateForTable($params, $page, $size);

        return [
            'data' => $paginator->getCollection()
                ->map(fn (FundCluster $fundCluster) => $this->datatableRowBuilder->build($fundCluster))
                ->values()
                ->all(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    public function optionsForSelect(): Collection
    {
        return $this->fundClusters->activeOptions();
    }

    public function create(string $actorUserId, array $data): FundCluster
    {
        return DB::transaction(function () use ($actorUserId, $data) {
            $fundCluster = $this->fundClusters->create([
                'code' => trim((string) ($data['code'] ?? '')),
                'name' => trim((string) ($data['name'] ?? '')),
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
            ]);

            $this->auditLogs->record(
                action: 'gso.fund_cluster.created',
                subject: $fundCluster,
                changesOld: [],
                changesNew: $fundCluster->only(['code', 'name', 'is_active']),
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO fund cluster created: ' . $this->label($fundCluster),
                display: $this->buildCreatedDisplay($fundCluster),
            );

            return $fundCluster;
        });
    }

    public function update(string $actorUserId, string $fundClusterId, array $data): FundCluster
    {
        return DB::transaction(function () use ($actorUserId, $fundClusterId, $data) {
            $fundCluster = $this->fundClusters->findOrFail($fundClusterId);
            $before = $fundCluster->only(['code', 'name', 'is_active']);

            $fundCluster->code = trim((string) ($data['code'] ?? $fundCluster->code));
            $fundCluster->name = trim((string) ($data['name'] ?? $fundCluster->name));
            $fundCluster->is_active = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : (bool) $fundCluster->is_active;

            $fundCluster = $this->fundClusters->save($fundCluster);
            $after = $fundCluster->only(['code', 'name', 'is_active']);

            $this->auditLogs->record(
                action: 'gso.fund_cluster.updated',
                subject: $fundCluster,
                changesOld: $before,
                changesNew: $after,
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO fund cluster updated: ' . $this->label($fundCluster),
                display: $this->buildUpdatedDisplay($before, $after),
            );

            return $fundCluster;
        });
    }

    public function delete(string $actorUserId, string $fundClusterId): void
    {
        DB::transaction(function () use ($actorUserId, $fundClusterId) {
            $fundCluster = $this->fundClusters->findOrFail($fundClusterId);
            $before = $fundCluster->only(['code', 'name', 'is_active']);

            $this->fundClusters->delete($fundCluster);

            $this->auditLogs->record(
                action: 'gso.fund_cluster.deleted',
                subject: $fundCluster,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO fund cluster archived: ' . $this->label($fundCluster),
                display: $this->buildLifecycleDisplay($fundCluster, 'Active Record', 'Archived'),
            );
        });
    }

    public function restore(string $actorUserId, string $fundClusterId): void
    {
        DB::transaction(function () use ($actorUserId, $fundClusterId) {
            $fundCluster = $this->fundClusters->findOrFail($fundClusterId, true);

            if (! $fundCluster->trashed()) {
                return;
            }

            $deletedAt = $fundCluster->deleted_at?->toDateTimeString();
            $this->fundClusters->restore($fundCluster);

            $this->auditLogs->record(
                action: 'gso.fund_cluster.restored',
                subject: $fundCluster,
                changesOld: ['deleted_at' => $deletedAt],
                changesNew: ['restored_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO fund cluster restored: ' . $this->label($fundCluster),
                display: $this->buildLifecycleDisplay($fundCluster, 'Archived', 'Active Record'),
            );
        });
    }

    private function buildCreatedDisplay(FundCluster $fundCluster): array
    {
        return [
            'summary' => 'Fund cluster created: ' . $this->label($fundCluster),
            'subject_label' => $this->label($fundCluster),
            'sections' => [[
                'title' => 'Fund Cluster Details',
                'items' => [
                    ['label' => 'Code', 'before' => 'None', 'after' => $fundCluster->code],
                    ['label' => 'Name', 'before' => 'None', 'after' => $fundCluster->name],
                    ['label' => 'Record Status', 'before' => 'None', 'after' => $fundCluster->is_active ? 'Active' : 'Inactive'],
                ],
            ]],
        ];
    }

    private function buildUpdatedDisplay(array $before, array $after): array
    {
        return [
            'summary' => 'Fund cluster updated: ' . $this->labelFromValues(
                (string) ($after['code'] ?? $before['code'] ?? ''),
                (string) ($after['name'] ?? $before['name'] ?? ''),
            ),
            'subject_label' => $this->labelFromValues(
                (string) ($after['code'] ?? $before['code'] ?? ''),
                (string) ($after['name'] ?? $before['name'] ?? ''),
            ),
            'sections' => [[
                'title' => 'Fund Cluster Details',
                'items' => [
                    ['label' => 'Code', 'before' => $before['code'] ?? 'None', 'after' => $after['code'] ?? 'None'],
                    ['label' => 'Name', 'before' => $before['name'] ?? 'None', 'after' => $after['name'] ?? 'None'],
                    [
                        'label' => 'Record Status',
                        'before' => array_key_exists('is_active', $before) ? ((bool) $before['is_active'] ? 'Active' : 'Inactive') : 'None',
                        'after' => array_key_exists('is_active', $after) ? ((bool) $after['is_active'] ? 'Active' : 'Inactive') : 'None',
                    ],
                ],
            ]],
        ];
    }

    private function buildLifecycleDisplay(FundCluster $fundCluster, string $before, string $after): array
    {
        return [
            'summary' => 'Fund cluster ' . strtolower($after === 'Archived' ? 'archived' : 'restored') . ': ' . $this->label($fundCluster),
            'subject_label' => $this->label($fundCluster),
            'sections' => [[
                'title' => 'Fund Cluster Lifecycle',
                'items' => [
                    ['label' => 'Archive Status', 'before' => $before, 'after' => $after],
                ],
            ]],
        ];
    }

    private function label(FundCluster $fundCluster): string
    {
        return $this->labelFromValues((string) $fundCluster->code, (string) $fundCluster->name);
    }

    private function labelFromValues(string $code, string $name): string
    {
        $code = trim($code);
        $name = trim($name);

        if ($code !== '' && $name !== '') {
            return "{$code} ({$name})";
        }

        if ($code !== '') {
            return $code;
        }

        if ($name !== '') {
            return $name;
        }

        return 'Fund Cluster';
    }
}
