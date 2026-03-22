<?php

namespace App\Modules\GSO\Services;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\Contracts\AssetCategoryDatatableRowBuilderInterface;
use App\Modules\GSO\Models\AssetCategory;
use App\Modules\GSO\Models\AssetType;
use App\Modules\GSO\Repositories\Contracts\AssetCategoryRepositoryInterface;
use App\Modules\GSO\Services\Contracts\AssetCategoryServiceInterface;
use Illuminate\Support\Facades\DB;

class AssetCategoryService implements AssetCategoryServiceInterface
{
    public function __construct(
        private readonly AssetCategoryRepositoryInterface $assetCategories,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly AssetCategoryDatatableRowBuilderInterface $datatableRowBuilder,
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));
        $paginator = $this->assetCategories->paginateForTable($params, $page, $size);

        return [
            'data' => $paginator->getCollection()
                ->map(fn (AssetCategory $assetCategory) => $this->datatableRowBuilder->build($assetCategory))
                ->values()
                ->all(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    public function create(string $actorUserId, array $data): AssetCategory
    {
        return DB::transaction(function () use ($actorUserId, $data) {
            $assetCategory = $this->assetCategories->create([
                'asset_type_id' => (string) ($data['asset_type_id'] ?? ''),
                'asset_code' => trim((string) ($data['asset_code'] ?? '')),
                'asset_name' => trim((string) ($data['asset_name'] ?? '')),
                'account_group' => $this->nullableString($data['account_group'] ?? null),
                'is_selected' => false,
            ])->load('type');

            $this->auditLogs->record(
                action: 'gso.asset_category.created',
                subject: $assetCategory,
                changesOld: [],
                changesNew: $assetCategory->only(['asset_type_id', 'asset_code', 'asset_name', 'account_group']),
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO asset category created: ' . $this->assetCategoryLabel($assetCategory),
                display: $this->buildCreatedDisplay($assetCategory),
            );

            return $assetCategory;
        });
    }

    public function update(string $actorUserId, string $assetCategoryId, array $data): AssetCategory
    {
        return DB::transaction(function () use ($actorUserId, $assetCategoryId, $data) {
            $assetCategory = $this->assetCategories->findOrFail($assetCategoryId);
            $before = $assetCategory->only(['asset_type_id', 'asset_code', 'asset_name', 'account_group']);

            $assetCategory->asset_type_id = (string) ($data['asset_type_id'] ?? $assetCategory->asset_type_id);
            $assetCategory->asset_code = trim((string) ($data['asset_code'] ?? $assetCategory->asset_code));
            $assetCategory->asset_name = trim((string) ($data['asset_name'] ?? $assetCategory->asset_name));
            $assetCategory->account_group = $this->nullableString($data['account_group'] ?? $assetCategory->account_group);

            $assetCategory = $this->assetCategories->save($assetCategory);
            $after = $assetCategory->only(['asset_type_id', 'asset_code', 'asset_name', 'account_group']);

            $this->auditLogs->record(
                action: 'gso.asset_category.updated',
                subject: $assetCategory,
                changesOld: $before,
                changesNew: $after,
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO asset category updated: ' . $this->assetCategoryLabel($assetCategory),
                display: $this->buildUpdatedDisplay($before, $after),
            );

            return $assetCategory;
        });
    }

    public function delete(string $actorUserId, string $assetCategoryId): void
    {
        DB::transaction(function () use ($actorUserId, $assetCategoryId) {
            $assetCategory = $this->assetCategories->findOrFail($assetCategoryId);
            $before = $assetCategory->only(['asset_type_id', 'asset_code', 'asset_name', 'account_group']);

            $this->assetCategories->delete($assetCategory);

            $this->auditLogs->record(
                action: 'gso.asset_category.deleted',
                subject: $assetCategory,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO asset category archived: ' . $this->assetCategoryLabel($assetCategory),
                display: $this->buildLifecycleDisplay($assetCategory, 'Active Record', 'Archived'),
            );
        });
    }

    public function restore(string $actorUserId, string $assetCategoryId): void
    {
        DB::transaction(function () use ($actorUserId, $assetCategoryId) {
            $assetCategory = $this->assetCategories->findOrFail($assetCategoryId, true);

            if (! $assetCategory->trashed()) {
                return;
            }

            $deletedAt = $assetCategory->deleted_at?->toDateTimeString();
            $this->assetCategories->restore($assetCategory);
            $assetCategory->load('type');

            $this->auditLogs->record(
                action: 'gso.asset_category.restored',
                subject: $assetCategory,
                changesOld: ['deleted_at' => $deletedAt],
                changesNew: ['restored_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO asset category restored: ' . $this->assetCategoryLabel($assetCategory),
                display: $this->buildLifecycleDisplay($assetCategory, 'Archived', 'Active Record'),
            );
        });
    }

    private function buildCreatedDisplay(AssetCategory $assetCategory): array
    {
        return [
            'summary' => 'Asset category created: ' . $this->assetCategoryLabel($assetCategory),
            'subject_label' => $this->assetCategoryLabel($assetCategory),
            'sections' => [[
                'title' => 'Asset Category Details',
                'items' => [
                    ['label' => 'Asset Type', 'before' => 'None', 'after' => $this->resolveAssetTypeLabel($assetCategory->asset_type_id)],
                    ['label' => 'Asset Code', 'before' => 'None', 'after' => $assetCategory->asset_code],
                    ['label' => 'Asset Name', 'before' => 'None', 'after' => $assetCategory->asset_name],
                    ['label' => 'Account Group', 'before' => 'None', 'after' => $this->nullableString($assetCategory->account_group) ?? 'None'],
                ],
            ]],
        ];
    }

    private function buildUpdatedDisplay(array $before, array $after): array
    {
        return [
            'summary' => 'Asset category updated: ' . $this->assetCategoryLabelFromValues(
                (string) ($after['asset_code'] ?? $before['asset_code'] ?? ''),
                (string) ($after['asset_name'] ?? $before['asset_name'] ?? ''),
            ),
            'subject_label' => $this->assetCategoryLabelFromValues(
                (string) ($after['asset_code'] ?? $before['asset_code'] ?? ''),
                (string) ($after['asset_name'] ?? $before['asset_name'] ?? ''),
            ),
            'sections' => [[
                'title' => 'Asset Category Details',
                'items' => [
                    ['label' => 'Asset Type', 'before' => $this->resolveAssetTypeLabel($before['asset_type_id'] ?? null), 'after' => $this->resolveAssetTypeLabel($after['asset_type_id'] ?? null)],
                    ['label' => 'Asset Code', 'before' => $before['asset_code'] ?? 'None', 'after' => $after['asset_code'] ?? 'None'],
                    ['label' => 'Asset Name', 'before' => $before['asset_name'] ?? 'None', 'after' => $after['asset_name'] ?? 'None'],
                    ['label' => 'Account Group', 'before' => $this->nullableString($before['account_group'] ?? null) ?? 'None', 'after' => $this->nullableString($after['account_group'] ?? null) ?? 'None'],
                ],
            ]],
        ];
    }

    private function buildLifecycleDisplay(AssetCategory $assetCategory, string $before, string $after): array
    {
        return [
            'summary' => 'Asset category ' . strtolower($after === 'Archived' ? 'archived' : 'restored') . ': ' . $this->assetCategoryLabel($assetCategory),
            'subject_label' => $this->assetCategoryLabel($assetCategory),
            'sections' => [[
                'title' => 'Asset Category Lifecycle',
                'items' => [
                    ['label' => 'Archive Status', 'before' => $before, 'after' => $after],
                ],
            ]],
            'request_details' => [
                'Asset Type' => $this->resolveAssetTypeLabel($assetCategory->asset_type_id),
            ],
        ];
    }

    private function assetCategoryLabel(AssetCategory $assetCategory): string
    {
        return $this->assetCategoryLabelFromValues((string) $assetCategory->asset_code, (string) $assetCategory->asset_name);
    }

    private function assetCategoryLabelFromValues(string $assetCode, string $assetName): string
    {
        $assetCode = trim($assetCode);
        $assetName = trim($assetName);

        if ($assetCode !== '' && $assetName !== '') {
            return "{$assetCode} ({$assetName})";
        }

        if ($assetCode !== '') {
            return $assetCode;
        }

        if ($assetName !== '') {
            return $assetName;
        }

        return 'Asset Category';
    }

    private function resolveAssetTypeLabel(?string $assetTypeId): string
    {
        $assetTypeId = trim((string) ($assetTypeId ?? ''));

        if ($assetTypeId === '') {
            return 'None';
        }

        $assetType = AssetType::query()
            ->withTrashed()
            ->select(['id', 'type_code', 'type_name'])
            ->find($assetTypeId);

        if (! $assetType) {
            return 'Unknown Asset Type';
        }

        $typeCode = trim((string) ($assetType->type_code ?? ''));
        $typeName = trim((string) ($assetType->type_name ?? ''));

        if ($typeCode !== '' && $typeName !== '') {
            return "{$typeCode} ({$typeName})";
        }

        return $typeCode !== '' ? $typeCode : ($typeName !== '' ? $typeName : 'Asset Type');
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
