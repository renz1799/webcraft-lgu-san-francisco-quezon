<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\StockMovement;
use App\Modules\GSO\Repositories\Contracts\StockMovementRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentStockMovementRepository implements StockMovementRepositoryInterface
{
    public function paginateForLedger(array $filters, string $itemId, int $page = 1, int $size = 15): LengthAwarePaginator
    {
        $query = StockMovement::query()
            ->with([
                'fundSource' => fn ($fundQuery) => $fundQuery
                    ->select(['id', 'code', 'name']),
            ])
            ->where('item_id', $itemId);

        $type = trim((string) ($filters['type'] ?? ''));
        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        $fundSourceId = trim((string) ($filters['fund_source_id'] ?? ''));

        if ($type !== '') {
            if ($type === 'adjust') {
                $query->where('movement_type', 'like', 'adjust_%');
            } else {
                $query->where('movement_type', $type);
            }
        }

        if ($dateFrom !== '') {
            $query->where('occurred_at', '>=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo !== '') {
            $query->where('occurred_at', '<=', $dateTo . ' 23:59:59');
        }

        if ($fundSourceId !== '') {
            $query->where('fund_source_id', $fundSourceId);
        }

        return $query
            ->orderByDesc('occurred_at')
            ->orderByDesc('created_at')
            ->paginate(
                perPage: max(1, min($size, 100)),
                columns: ['*'],
                pageName: 'page',
                page: max(1, $page),
            );
    }

    public function getForCard(
        string $itemId,
        ?string $fundSourceId = null,
        bool $includeNullFundSource = false,
        ?CarbonInterface $asOf = null
    ): Collection {
        $query = StockMovement::query()
            ->with([
                'fundSource' => fn ($fundQuery) => $fundQuery
                    ->select(['id', 'code', 'name']),
            ])
            ->where('item_id', $itemId);

        if ($fundSourceId === null || trim($fundSourceId) === '') {
            $query->whereNull('fund_source_id');
        } elseif ($includeNullFundSource) {
            $query->where(function (Builder $fundQuery) use ($fundSourceId) {
                $fundQuery->where('fund_source_id', $fundSourceId)
                    ->orWhereNull('fund_source_id');
            });
        } else {
            $query->where('fund_source_id', $fundSourceId);
        }

        if ($asOf) {
            $query->where('occurred_at', '<=', $asOf);
        }

        return $query
            ->orderBy('occurred_at')
            ->orderBy('created_at')
            ->get();
    }

    public function create(array $data): StockMovement
    {
        return StockMovement::query()->create($data)->load('fundSource');
    }
}
