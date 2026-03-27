<?php

namespace App\Modules\GSO\Services\Contracts\ICS;

use App\Modules\GSO\Models\IcsItem;

interface IcsItemServiceInterface
{
    public function suggestItems(string $icsId, string $query): array;

    public function listForEdit(string $icsId): array;

    public function addItem(string $actorUserId, string $icsId, string $inventoryItemId): IcsItem;

    public function removeItem(string $actorUserId, string $icsId, string $icsItemId): void;
}
