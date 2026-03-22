<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\InventoryItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryItemRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): InventoryItem;

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator;

    public function create(array $data): InventoryItem;

    public function save(InventoryItem $inventoryItem): InventoryItem;

    public function delete(InventoryItem $inventoryItem): void;

    public function restore(InventoryItem $inventoryItem): void;
}
