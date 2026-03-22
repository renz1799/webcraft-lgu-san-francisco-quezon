<?php

namespace App\Modules\GSO\Services\Contracts;

use App\Modules\GSO\Models\InventoryItem;

interface InventoryItemCardPrintServiceInterface
{
    /**
     * @return array{view: string, data: array<string, mixed>}
     */
    public function getPropertyCardPrintPayload(InventoryItem $inventoryItem, array $options = []): array;
}
