<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Repositories\Contracts\FundSourceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentFundSourceRepository implements FundSourceRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): FundSource
    {
        $query = FundSource::query()->with([
            'fundCluster' => fn ($clusterQuery) => $clusterQuery
                ->withTrashed()
                ->select(['id', 'code', 'name']),
        ]);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator
    {
        $query = FundSource::query()
            ->with([
                'fundCluster' => fn ($clusterQuery) => $clusterQuery
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
            ]);

        $archived = $this->resolveArchivedMode($filters);
        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));
        $fundClusterId = trim((string) ($filters['fund_cluster_id'] ?? ''));

        if ($archived === 'all') {
            $query->withTrashed();
        } elseif ($archived === 'archived') {
            $query->onlyTrashed();
        }

        if ($fundClusterId !== '') {
            $query->where('fund_cluster_id', $fundClusterId);
        }

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('fundCluster', function (Builder $clusterQuery) use ($search) {
                        $clusterQuery->withTrashed()
                            ->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query
            ->orderBy('name')
            ->paginate(
                perPage: max(1, min($size, 100)),
                columns: ['*'],
                pageName: 'page',
                page: max(1, $page),
            );
    }

    public function create(array $data): FundSource
    {
        return FundSource::query()->create($data)->load('fundCluster');
    }

    public function save(FundSource $fundSource): FundSource
    {
        $fundSource->save();

        return $fundSource->refresh()->load('fundCluster');
    }

    public function delete(FundSource $fundSource): void
    {
        $fundSource->delete();
    }

    public function restore(FundSource $fundSource): void
    {
        $fundSource->restore();
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
