<?php

namespace App\Modules\GSO\Services\Contracts\PTR;

use App\Modules\GSO\Models\PtrItem;

interface PtrItemServiceInterface
{
    public function suggestItems(string $ptrId, string $query): array;

    public function listForEdit(string $ptrId): array;

    public function addItem(string $actorUserId, string $ptrId, string $inventoryItemId): PtrItem;

    public function removeItem(string $actorUserId, string $ptrId, string $ptrItemId): void;
}
