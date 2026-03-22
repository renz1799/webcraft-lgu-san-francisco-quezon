<?php

namespace App\Modules\GSO\Services;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\Contracts\FundSourceDatatableRowBuilderInterface;
use App\Modules\GSO\Models\FundCluster;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Repositories\Contracts\FundSourceRepositoryInterface;
use App\Modules\GSO\Services\Contracts\FundSourceServiceInterface;
use Illuminate\Support\Facades\DB;

class FundSourceService implements FundSourceServiceInterface
{
    public function __construct(
        private readonly FundSourceRepositoryInterface $fundSources,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly FundSourceDatatableRowBuilderInterface $datatableRowBuilder,
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));
        $paginator = $this->fundSources->paginateForTable($params, $page, $size);

        return [
            'data' => $paginator->getCollection()
                ->map(fn (FundSource $fundSource) => $this->datatableRowBuilder->build($fundSource))
                ->values()
                ->all(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    public function create(string $actorUserId, array $data): FundSource
    {
        return DB::transaction(function () use ($actorUserId, $data) {
            $fundSource = $this->fundSources->create([
                'code' => $this->nullableString($data['code'] ?? null),
                'name' => trim((string) ($data['name'] ?? '')),
                'fund_cluster_id' => $this->nullableString($data['fund_cluster_id'] ?? null),
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
            ]);

            $this->auditLogs->record(
                action: 'gso.fund_source.created',
                subject: $fundSource,
                changesOld: [],
                changesNew: $fundSource->only(['code', 'name', 'fund_cluster_id', 'is_active']),
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO fund source created: ' . $this->label($fundSource),
                display: $this->buildCreatedDisplay($fundSource),
            );

            return $fundSource;
        });
    }

    public function update(string $actorUserId, string $fundSourceId, array $data): FundSource
    {
        return DB::transaction(function () use ($actorUserId, $fundSourceId, $data) {
            $fundSource = $this->fundSources->findOrFail($fundSourceId);
            $before = $fundSource->only(['code', 'name', 'fund_cluster_id', 'is_active']);

            $fundSource->code = $this->nullableString($data['code'] ?? $fundSource->code);
            $fundSource->name = trim((string) ($data['name'] ?? $fundSource->name));
            $fundSource->fund_cluster_id = $this->nullableString($data['fund_cluster_id'] ?? $fundSource->fund_cluster_id);
            $fundSource->is_active = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : (bool) $fundSource->is_active;

            $fundSource = $this->fundSources->save($fundSource);
            $after = $fundSource->only(['code', 'name', 'fund_cluster_id', 'is_active']);

            $this->auditLogs->record(
                action: 'gso.fund_source.updated',
                subject: $fundSource,
                changesOld: $before,
                changesNew: $after,
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO fund source updated: ' . $this->label($fundSource),
                display: $this->buildUpdatedDisplay($before, $after),
            );

            return $fundSource;
        });
    }

    public function delete(string $actorUserId, string $fundSourceId): void
    {
        DB::transaction(function () use ($actorUserId, $fundSourceId) {
            $fundSource = $this->fundSources->findOrFail($fundSourceId);
            $before = $fundSource->only(['code', 'name', 'fund_cluster_id', 'is_active']);

            $this->fundSources->delete($fundSource);

            $this->auditLogs->record(
                action: 'gso.fund_source.deleted',
                subject: $fundSource,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO fund source archived: ' . $this->label($fundSource),
                display: $this->buildLifecycleDisplay($fundSource, 'Active Record', 'Archived'),
            );
        });
    }

    public function restore(string $actorUserId, string $fundSourceId): void
    {
        DB::transaction(function () use ($actorUserId, $fundSourceId) {
            $fundSource = $this->fundSources->findOrFail($fundSourceId, true);

            if (! $fundSource->trashed()) {
                return;
            }

            $deletedAt = $fundSource->deleted_at?->toDateTimeString();
            $this->fundSources->restore($fundSource);

            $this->auditLogs->record(
                action: 'gso.fund_source.restored',
                subject: $fundSource,
                changesOld: ['deleted_at' => $deletedAt],
                changesNew: ['restored_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO fund source restored: ' . $this->label($fundSource),
                display: $this->buildLifecycleDisplay($fundSource, 'Archived', 'Active Record'),
            );
        });
    }

    private function buildCreatedDisplay(FundSource $fundSource): array
    {
        return [
            'summary' => 'Fund source created: ' . $this->label($fundSource),
            'subject_label' => $this->label($fundSource),
            'sections' => [[
                'title' => 'Fund Source Details',
                'items' => [
                    ['label' => 'Code', 'before' => 'None', 'after' => $fundSource->code ?? 'None'],
                    ['label' => 'Name', 'before' => 'None', 'after' => $fundSource->name],
                    ['label' => 'Fund Cluster', 'before' => 'None', 'after' => $this->resolveFundClusterLabel($fundSource->fund_cluster_id)],
                    ['label' => 'Record Status', 'before' => 'None', 'after' => $fundSource->is_active ? 'Active' : 'Inactive'],
                ],
            ]],
        ];
    }

    private function buildUpdatedDisplay(array $before, array $after): array
    {
        return [
            'summary' => 'Fund source updated: ' . $this->labelFromValues(
                (string) ($after['code'] ?? $before['code'] ?? ''),
                (string) ($after['name'] ?? $before['name'] ?? ''),
            ),
            'subject_label' => $this->labelFromValues(
                (string) ($after['code'] ?? $before['code'] ?? ''),
                (string) ($after['name'] ?? $before['name'] ?? ''),
            ),
            'sections' => [[
                'title' => 'Fund Source Details',
                'items' => [
                    ['label' => 'Code', 'before' => $before['code'] ?? 'None', 'after' => $after['code'] ?? 'None'],
                    ['label' => 'Name', 'before' => $before['name'] ?? 'None', 'after' => $after['name'] ?? 'None'],
                    ['label' => 'Fund Cluster', 'before' => $this->resolveFundClusterLabel($before['fund_cluster_id'] ?? null), 'after' => $this->resolveFundClusterLabel($after['fund_cluster_id'] ?? null)],
                    [
                        'label' => 'Record Status',
                        'before' => array_key_exists('is_active', $before) ? ((bool) $before['is_active'] ? 'Active' : 'Inactive') : 'None',
                        'after' => array_key_exists('is_active', $after) ? ((bool) $after['is_active'] ? 'Active' : 'Inactive') : 'None',
                    ],
                ],
            ]],
        ];
    }

    private function buildLifecycleDisplay(FundSource $fundSource, string $before, string $after): array
    {
        return [
            'summary' => 'Fund source ' . strtolower($after === 'Archived' ? 'archived' : 'restored') . ': ' . $this->label($fundSource),
            'subject_label' => $this->label($fundSource),
            'sections' => [[
                'title' => 'Fund Source Lifecycle',
                'items' => [
                    ['label' => 'Archive Status', 'before' => $before, 'after' => $after],
                ],
            ]],
        ];
    }

    private function label(FundSource $fundSource): string
    {
        return $this->labelFromValues((string) ($fundSource->code ?? ''), (string) $fundSource->name);
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

        return 'Fund Source';
    }

    private function resolveFundClusterLabel(?string $fundClusterId): string
    {
        $fundClusterId = trim((string) ($fundClusterId ?? ''));

        if ($fundClusterId === '') {
            return 'None';
        }

        $fundCluster = FundCluster::query()
            ->withTrashed()
            ->select(['id', 'code', 'name'])
            ->find($fundClusterId);

        if (! $fundCluster) {
            return 'Unknown Fund Cluster';
        }

        $code = trim((string) $fundCluster->code);
        $name = trim((string) $fundCluster->name);

        if ($code !== '' && $name !== '') {
            return "{$code} ({$name})";
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Fund Cluster');
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
