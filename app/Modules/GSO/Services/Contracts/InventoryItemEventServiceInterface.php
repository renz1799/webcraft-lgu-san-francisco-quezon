<?php

namespace App\Modules\GSO\Services\Contracts;

use App\Modules\GSO\Models\InventoryItemEvent;

interface InventoryItemEventServiceInterface
{
    /**
     * @return array{inventory_item: array<string, mixed>, events: array<int, array<string, mixed>>}
     */
    public function listForInventoryItem(string $inventoryItemId): array;

    public function create(string $actorUserId, string $inventoryItemId, array $data): InventoryItemEvent;
}
