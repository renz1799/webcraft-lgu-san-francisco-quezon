<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\InventoryItemEvent;
use Illuminate\Support\Collection;

interface InventoryItemEventRepositoryInterface
{
    /**
     * @return Collection<int, InventoryItemEvent>
     */
    public function listForInventoryItem(string $inventoryItemId, bool $withTrashed = false): Collection;

    /**
     * @return Collection<int, InventoryItemEvent>
     */
    public function getForPropertyCard(string $inventoryItemId): Collection;

    public function create(array $data): InventoryItemEvent;
}
