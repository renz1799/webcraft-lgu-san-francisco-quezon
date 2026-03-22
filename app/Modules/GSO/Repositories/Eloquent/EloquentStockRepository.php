<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Models\Stock;
use App\Modules\GSO\Models\StockMovement;
use App\Modules\GSO\Repositories\Contracts\StockRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentStockRepository implements StockRepositoryInterface
{
    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator
    {
        $query = Item::query()
            ->where('tracking_type', 'consumable')
            ->with([
                'stocks' => fn ($stockQuery) => $stockQuery
                    ->with([
                        'fundSource' => fn ($fundQuery) => $fundQuery
                            ->with([
                                'fundCluster' => fn ($clusterQuery) => $clusterQuery
                                    ->select(['id', 'code', 'name']),
                            ])
                            ->select(['id', 'fund_cluster_id', 'code', 'name']),
                    ])
                    ->orderBy('fund_source_id'),
            ])
            ->withSum('stocks as on_hand_total', 'on_hand')
            ->selectSub(
                StockMovement::query()
                    ->withoutGlobalScopes()
                    ->selectRaw('MAX(occurred_at)')
                    ->whereColumn('item_id', 'items.id')
                    ->whereNull('deleted_at'),
                'last_movement_at'
            );

        $archived = $this->resolveArchivedMode($filters);
        if ($archived === 'all') {
            $query->withTrashed();
        } elseif ($archived === 'archived') {
            $query->onlyTrashed();
        }

        $search = trim((string) ($filters['search'] ?? ''));
        $fundSourceId = trim((string) ($filters['fund_source_id'] ?? ''));
        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        $onHandMin = $filters['onhand_min'] ?? null;
        $onHandMax = $filters['onhand_max'] ?? null;

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('item_name', 'like', "%{$search}%")
                    ->orWhere('item_identification', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('base_unit', 'like', "%{$search}%");
            });
        }

        if ($fundSourceId !== '') {
            $query->whereHas('stocks', function (Builder $stockQuery) use ($fundSourceId) {
                $stockQuery->where('fund_source_id', $fundSourceId);
            });
        }

        if ($dateFrom !== '') {
            $query->whereRaw(
                'COALESCE((select max(sm.occurred_at) from stock_movements sm where sm.item_id = items.id and sm.deleted_at is null), ?) >= ?',
                ['', $dateFrom . ' 00:00:00']
            );
        }

        if ($dateTo !== '') {
            $query->whereRaw(
                'COALESCE((select max(sm.occurred_at) from stock_movements sm where sm.item_id = items.id and sm.deleted_at is null), ?) <= ?',
                ['', $dateTo . ' 23:59:59']
            );
        }

        if ($onHandMin !== null && $onHandMin !== '') {
            $query->whereRaw(
                'COALESCE((select sum(s.on_hand) from stocks s where s.item_id = items.id and s.deleted_at is null), 0) >= ?',
                [(int) $onHandMin]
            );
        }

        if ($onHandMax !== null && $onHandMax !== '') {
            $query->whereRaw(
                'COALESCE((select sum(s.on_hand) from stocks s where s.item_id = items.id and s.deleted_at is null), 0) <= ?',
                [(int) $onHandMax]
            );
        }

        return $query
            ->orderByRaw('case when last_movement_at is null then 1 else 0 end')
            ->orderByDesc('last_movement_at')
            ->orderBy('item_name')
            ->paginate(
                perPage: max(1, min($size, 100)),
                columns: ['*'],
                pageName: 'page',
                page: max(1, $page),
            );
    }

    public function findByItemAndFundSource(
        string $itemId,
        ?string $fundSourceId,
        bool $lockForUpdate = false,
        bool $withTrashed = false
    ): ?Stock {
        $query = Stock::query()
            ->with([
                'fundSource' => fn ($fundQuery) => $fundQuery
                    ->with([
                        'fundCluster' => fn ($clusterQuery) => $clusterQuery
                            ->select(['id', 'code', 'name']),
                    ])
                    ->select(['id', 'fund_cluster_id', 'code', 'name']),
            ])
            ->where('item_id', $itemId);

        if ($fundSourceId === null || trim($fundSourceId) === '') {
            $query->whereNull('fund_source_id');
        } else {
            $query->where('fund_source_id', $fundSourceId);
        }

        if ($withTrashed) {
            $query->withTrashed();
        }

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function activeRowsForItem(string $itemId): Collection
    {
        return Stock::query()
            ->with([
                'fundSource' => fn ($fundQuery) => $fundQuery
                    ->with([
                        'fundCluster' => fn ($clusterQuery) => $clusterQuery
                            ->select(['id', 'code', 'name']),
                    ])
                    ->select(['id', 'fund_cluster_id', 'code', 'name']),
            ])
            ->where('item_id', $itemId)
            ->orderBy('fund_source_id')
            ->get();
    }

    public function create(array $data): Stock
    {
        return Stock::query()->create($data)->load('fundSource.fundCluster');
    }

    public function save(Stock $stock): Stock
    {
        $stock->save();

        return $stock->refresh()->load('fundSource.fundCluster');
    }

    public function restore(Stock $stock): void
    {
        $stock->restore();
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
