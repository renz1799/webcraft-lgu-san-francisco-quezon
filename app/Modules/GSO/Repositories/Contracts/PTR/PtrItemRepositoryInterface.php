<?php

namespace App\Modules\GSO\Repositories\Contracts\PTR;

use App\Modules\GSO\Models\PtrItem;
use Illuminate\Support\Collection;

interface PtrItemRepositoryInterface
{
    public function suggestTransferableItems(string $ptrId, string $q, int $limit = 10): array;

    public function addInventoryItemToPtr(string $ptrId, string $inventoryItemId): PtrItem;

    public function listByPtrId(string $ptrId): Collection;

    public function findById(string $id): ?PtrItem;

    public function delete(PtrItem $ptrItem): void;
}
