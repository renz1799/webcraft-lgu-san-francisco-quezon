<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\StockMovement;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface StockMovementRepositoryInterface
{
    public function paginateForLedger(array $filters, string $itemId, int $page = 1, int $size = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, StockMovement>
     */
    public function getForCard(
        string $itemId,
        ?string $fundSourceId = null,
        bool $includeNullFundSource = false,
        ?CarbonInterface $asOf = null
    ): Collection;

    public function create(array $data): StockMovement;
}
