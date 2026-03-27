<?php

namespace App\Modules\GSO\Services\Contracts\PAR;

use App\Modules\GSO\Models\ParItem;

interface ParItemServiceInterface
{
    public function suggestItems(string $parId, string $q): array;

    public function addItem(string $actorUserId, string $parId, string $inventoryItemId, int $quantity = 1): ParItem;
}
