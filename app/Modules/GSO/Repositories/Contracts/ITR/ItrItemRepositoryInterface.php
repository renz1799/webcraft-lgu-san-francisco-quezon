<?php

namespace App\Modules\GSO\Repositories\Contracts\ITR;

use App\Modules\GSO\Models\ItrItem;
use Illuminate\Support\Collection;

interface ItrItemRepositoryInterface
{
    public function suggestTransferableItems(string $itrId, string $q, int $limit = 10): array;

    public function addInventoryItemToItr(string $itrId, string $inventoryItemId): ItrItem;

    public function listByItrId(string $itrId): Collection;

    public function findById(string $id): ?ItrItem;

    public function delete(ItrItem $itrItem): void;
}



