<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\FundCluster;
use App\Modules\GSO\Repositories\Contracts\FundClusterRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentFundClusterRepository implements FundClusterRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): FundCluster
    {
        $query = FundCluster::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator
    {
        $query = FundCluster::query();
        $archived = $this->resolveArchivedMode($filters);
        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));

        if ($archived === 'all') {
            $query->withTrashed();
        } elseif ($archived === 'archived') {
            $query->onlyTrashed();
        }

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy('code')
            ->paginate(
                perPage: max(1, min($size, 100)),
                columns: ['*'],
                pageName: 'page',
                page: max(1, $page),
            );
    }

    public function activeOptions(): Collection
    {
        return FundCluster::query()
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->select(['id', 'code', 'name'])
            ->orderBy('code')
            ->get();
    }

    public function create(array $data): FundCluster
    {
        return FundCluster::query()->create($data);
    }

    public function save(FundCluster $fundCluster): FundCluster
    {
        $fundCluster->save();

        return $fundCluster->refresh();
    }

    public function delete(FundCluster $fundCluster): void
    {
        $fundCluster->delete();
    }

    public function restore(FundCluster $fundCluster): void
    {
        $fundCluster->restore();
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
