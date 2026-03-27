<?php

namespace App\Modules\GSO\Repositories\Contracts\WMR;

use App\Modules\GSO\Models\WmrItem;
use Illuminate\Support\Collection;

interface WmrItemRepositoryInterface
{
    public function suggestDisposableItems(string $wmrId, string $q, int $limit = 10): array;

    public function addInventoryItemToWmr(string $wmrId, string $inventoryItemId): WmrItem;

    public function updateLine(WmrItem $wmrItem, array $attributes): WmrItem;

    public function listByWmrId(string $wmrId): Collection;

    public function findById(string $id): ?WmrItem;

    public function delete(WmrItem $wmrItem): void;
}
