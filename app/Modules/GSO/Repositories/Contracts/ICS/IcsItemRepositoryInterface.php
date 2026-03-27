<?php

namespace App\Modules\GSO\Repositories\Contracts\ICS;

use App\Modules\GSO\Models\IcsItem;
use Illuminate\Support\Collection;

interface IcsItemRepositoryInterface
{
    public function suggestFromGsoPool(string $icsId, string $q, int $limit = 10): array;

    public function addInventoryItemToIcs(string $icsId, string $inventoryItemId): IcsItem;

    public function listByIcsId(string $icsId): Collection;

    public function findById(string $id): ?IcsItem;

    public function delete(IcsItem $icsItem): void;
}
