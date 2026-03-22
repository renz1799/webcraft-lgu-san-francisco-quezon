<?php

namespace App\Modules\GSO\Builders\Contracts;

use App\Modules\GSO\Models\InventoryItem;

interface InventoryItemDatatableRowBuilderInterface
{
    public function build(InventoryItem $inventoryItem): array;
}
