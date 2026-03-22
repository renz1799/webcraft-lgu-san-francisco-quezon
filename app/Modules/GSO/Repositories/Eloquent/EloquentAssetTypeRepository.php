<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\AssetType;
use App\Modules\GSO\Repositories\Contracts\AssetTypeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentAssetTypeRepository implements AssetTypeRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): AssetType
    {
        $query = AssetType::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator
    {
        $query = AssetType::query();
        $archived = $this->resolveArchivedMode($filters);
        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));

        if ($archived === 'all') {
            $query->withTrashed();
        } elseif ($archived === 'archived') {
            $query->onlyTrashed();
        }

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('type_code', 'like', "%{$search}%")
                    ->orWhere('type_name', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy('type_code')
            ->paginate(
                perPage: max(1, min($size, 100)),
                columns: ['*'],
                pageName: 'page',
                page: max(1, $page),
            );
    }

    public function activeOptions(): Collection
    {
        return AssetType::query()
            ->select(['id', 'type_code', 'type_name'])
            ->orderBy('type_code')
            ->get();
    }

    public function create(array $data): AssetType
    {
        return AssetType::query()->create($data);
    }

    public function save(AssetType $assetType): AssetType
    {
        $assetType->save();

        return $assetType->refresh();
    }

    public function delete(AssetType $assetType): void
    {
        $assetType->delete();
    }

    public function restore(AssetType $assetType): void
    {
        $assetType->restore();
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
