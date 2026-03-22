<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\Stock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface StockRepositoryInterface
{
    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator;

    public function findByItemAndFundSource(
        string $itemId,
        ?string $fundSourceId,
        bool $lockForUpdate = false,
        bool $withTrashed = false
    ): ?Stock;

    /**
     * @return Collection<int, Stock>
     */
    public function activeRowsForItem(string $itemId): Collection;

    public function create(array $data): Stock;

    public function save(Stock $stock): Stock;

    public function restore(Stock $stock): void;
}
