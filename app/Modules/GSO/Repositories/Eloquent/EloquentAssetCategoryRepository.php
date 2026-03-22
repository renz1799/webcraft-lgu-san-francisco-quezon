<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\AssetCategory;
use App\Modules\GSO\Repositories\Contracts\AssetCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentAssetCategoryRepository implements AssetCategoryRepositoryInterface
{
    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator
    {
        $query = AssetCategory::query()
            ->with([
                'type' => fn ($typeQuery) => $typeQuery->select(['id', 'type_code', 'type_name']),
            ]);

        $archived = $this->resolveArchivedMode($filters);
        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));
        $assetTypeId = trim((string) ($filters['asset_type_id'] ?? ''));

        if ($archived === 'all') {
            $query->withTrashed();
        } elseif ($archived === 'archived') {
            $query->onlyTrashed();
        }

        if ($assetTypeId !== '') {
            $query->where('asset_type_id', $assetTypeId);
        }

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('asset_code', 'like', "%{$search}%")
                    ->orWhere('asset_name', 'like', "%{$search}%")
                    ->orWhere('account_group', 'like', "%{$search}%")
                    ->orWhereHas('type', function (Builder $typeQuery) use ($search) {
                        $typeQuery->where('type_code', 'like', "%{$search}%")
                            ->orWhere('type_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query
            ->orderBy('asset_code')
            ->paginate(
                perPage: max(1, min($size, 100)),
                columns: ['*'],
                pageName: 'page',
                page: max(1, $page),
            );
    }

    public function create(array $data): AssetCategory
    {
        return AssetCategory::query()->create($data);
    }

    public function findOrFail(string $id, bool $withTrashed = false): AssetCategory
    {
        $query = AssetCategory::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function save(AssetCategory $assetCategory): AssetCategory
    {
        $assetCategory->save();

        return $assetCategory->refresh()->load('type');
    }

    public function delete(AssetCategory $assetCategory): void
    {
        $assetCategory->delete();
    }

    public function restore(AssetCategory $assetCategory): void
    {
        $assetCategory->restore();
    }

    private function resolveArchivedMode(array $filters): string
    {
        $mode = trim((string) ($filters['archived'] ?? $filters['status'] ?? 'active'));

        if (in_array($mode, ['active', 'archived', 'all'], true)) {
            return $mode;
        }

        return 'active';
    }
}
