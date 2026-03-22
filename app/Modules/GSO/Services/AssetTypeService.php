<?php

namespace App\Modules\GSO\Services;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\Contracts\AssetTypeDatatableRowBuilderInterface;
use App\Modules\GSO\Models\AssetType;
use App\Modules\GSO\Repositories\Contracts\AssetTypeRepositoryInterface;
use App\Modules\GSO\Services\Contracts\AssetTypeServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AssetTypeService implements AssetTypeServiceInterface
{
    public function __construct(
        private readonly AssetTypeRepositoryInterface $assetTypes,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly AssetTypeDatatableRowBuilderInterface $datatableRowBuilder,
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));

        $paginator = $this->assetTypes->paginateForTable($params, $page, $size);

        return [
            'data' => $paginator->getCollection()
                ->map(fn (AssetType $assetType) => $this->datatableRowBuilder->build($assetType))
                ->values()
                ->all(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    public function optionsForSelect(): Collection
    {
        return $this->assetTypes->activeOptions();
    }

    public function create(string $actorUserId, array $data): AssetType
    {
        return DB::transaction(function () use ($actorUserId, $data) {
            $assetType = $this->assetTypes->create([
                'type_code' => trim((string) ($data['type_code'] ?? '')),
                'type_name' => trim((string) ($data['type_name'] ?? '')),
            ]);

            $this->auditLogs->record(
                action: 'gso.asset_type.created',
                subject: $assetType,
                changesOld: [],
                changesNew: $assetType->only(['type_code', 'type_name']),
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO asset type created: ' . $this->assetTypeLabel($assetType),
                display: $this->buildCreatedDisplay($assetType),
            );

            return $assetType;
        });
    }

    public function update(string $actorUserId, string $assetTypeId, array $data): AssetType
    {
        return DB::transaction(function () use ($actorUserId, $assetTypeId, $data) {
            $assetType = $this->assetTypes->findOrFail($assetTypeId);
            $before = $assetType->only(['type_code', 'type_name']);

            $assetType->type_code = trim((string) ($data['type_code'] ?? $assetType->type_code));
            $assetType->type_name = trim((string) ($data['type_name'] ?? $assetType->type_name));

            $assetType = $this->assetTypes->save($assetType);
            $after = $assetType->only(['type_code', 'type_name']);

            $this->auditLogs->record(
                action: 'gso.asset_type.updated',
                subject: $assetType,
                changesOld: $before,
                changesNew: $after,
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO asset type updated: ' . $this->assetTypeLabel($assetType),
                display: $this->buildUpdatedDisplay($before, $after),
            );

            return $assetType;
        });
    }

    public function delete(string $actorUserId, string $assetTypeId): void
    {
        DB::transaction(function () use ($actorUserId, $assetTypeId) {
            $assetType = $this->assetTypes->findOrFail($assetTypeId);
            $before = $assetType->only(['type_code', 'type_name']);

            $this->assetTypes->delete($assetType);

            $this->auditLogs->record(
                action: 'gso.asset_type.deleted',
                subject: $assetType,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO asset type archived: ' . $this->assetTypeLabel($assetType),
                display: $this->buildLifecycleDisplay($assetType, 'Active Record', 'Archived'),
            );
        });
    }

    public function restore(string $actorUserId, string $assetTypeId): void
    {
        DB::transaction(function () use ($actorUserId, $assetTypeId) {
            $assetType = $this->assetTypes->findOrFail($assetTypeId, true);

            if (! $assetType->trashed()) {
                return;
            }

            $deletedAt = $assetType->deleted_at?->toDateTimeString();
            $this->assetTypes->restore($assetType);

            $this->auditLogs->record(
                action: 'gso.asset_type.restored',
                subject: $assetType,
                changesOld: ['deleted_at' => $deletedAt],
                changesNew: ['restored_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO asset type restored: ' . $this->assetTypeLabel($assetType),
                display: $this->buildLifecycleDisplay($assetType, 'Archived', 'Active Record'),
            );
        });
    }

    private function buildCreatedDisplay(AssetType $assetType): array
    {
        return [
            'summary' => 'Asset type created: ' . $this->assetTypeLabel($assetType),
            'subject_label' => $this->assetTypeLabel($assetType),
            'sections' => [[
                'title' => 'Asset Type Details',
                'items' => [
                    ['label' => 'Type Code', 'before' => 'None', 'after' => $assetType->type_code],
                    ['label' => 'Type Name', 'before' => 'None', 'after' => $assetType->type_name],
                ],
            ]],
        ];
    }

    private function buildUpdatedDisplay(array $before, array $after): array
    {
        return [
            'summary' => 'Asset type updated: ' . $this->assetTypeLabelFromValues(
                (string) ($after['type_code'] ?? $before['type_code'] ?? ''),
                (string) ($after['type_name'] ?? $before['type_name'] ?? ''),
            ),
            'subject_label' => $this->assetTypeLabelFromValues(
                (string) ($after['type_code'] ?? $before['type_code'] ?? ''),
                (string) ($after['type_name'] ?? $before['type_name'] ?? ''),
            ),
            'sections' => [[
                'title' => 'Asset Type Details',
                'items' => [
                    ['label' => 'Type Code', 'before' => $before['type_code'] ?? 'None', 'after' => $after['type_code'] ?? 'None'],
                    ['label' => 'Type Name', 'before' => $before['type_name'] ?? 'None', 'after' => $after['type_name'] ?? 'None'],
                ],
            ]],
        ];
    }

    private function buildLifecycleDisplay(AssetType $assetType, string $before, string $after): array
    {
        return [
            'summary' => 'Asset type ' . strtolower($after === 'Archived' ? 'archived' : 'restored') . ': ' . $this->assetTypeLabel($assetType),
            'subject_label' => $this->assetTypeLabel($assetType),
            'sections' => [[
                'title' => 'Asset Type Lifecycle',
                'items' => [
                    ['label' => 'Archive Status', 'before' => $before, 'after' => $after],
                ],
            ]],
        ];
    }

    private function assetTypeLabel(AssetType $assetType): string
    {
        return $this->assetTypeLabelFromValues((string) $assetType->type_code, (string) $assetType->type_name);
    }

    private function assetTypeLabelFromValues(string $typeCode, string $typeName): string
    {
        $typeCode = trim($typeCode);
        $typeName = trim($typeName);

        if ($typeCode !== '' && $typeName !== '') {
            return "{$typeCode} ({$typeName})";
        }

        if ($typeCode !== '') {
            return $typeCode;
        }

        if ($typeName !== '') {
            return $typeName;
        }

        return 'Asset Type';
    }
}
