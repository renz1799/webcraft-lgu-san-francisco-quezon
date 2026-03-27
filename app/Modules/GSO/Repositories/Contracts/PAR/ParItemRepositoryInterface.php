<?php

namespace App\Modules\GSO\Repositories\Contracts\PAR;

use App\Modules\GSO\Models\ParItem;
use Illuminate\Support\Collection;

interface ParItemRepositoryInterface
{
    public function add(array $data): ParItem;

    public function remove(string $parId, string $inventoryItemId): void;

    public function delete(ParItem $parItem): void;

    public function listByParId(string $parId): Collection;

    public function exists(string $parId, string $inventoryItemId): bool;

    public function suggestFromGsoPool(string $parId, string $q, int $limit = 10): array;

    public function addInventoryItemToPar(string $parId, string $inventoryItemId, int $quantity = 1): ParItem;
}
