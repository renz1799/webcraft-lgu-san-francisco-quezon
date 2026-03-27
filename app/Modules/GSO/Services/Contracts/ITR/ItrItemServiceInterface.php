<?php

namespace App\Modules\GSO\Services\Contracts\ITR;

use App\Modules\GSO\Models\ItrItem;

interface ItrItemServiceInterface
{
    public function suggestItems(string $itrId, string $query): array;

    public function listForEdit(string $itrId): array;

    public function addItem(string $actorUserId, string $itrId, string $inventoryItemId): ItrItem;

    public function removeItem(string $actorUserId, string $itrId, string $itrItemId): void;
}



