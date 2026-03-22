<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\InventoryItemFile;
use Illuminate\Support\Collection;

interface InventoryItemFileRepositoryInterface
{
    /**
     * @return Collection<int, InventoryItemFile>
     */
    public function listForInventoryItem(string $inventoryItemId, bool $withTrashed = false): Collection;

    public function findForInventoryItemOrFail(string $inventoryItemId, string $fileId, bool $withTrashed = false): InventoryItemFile;

    public function create(array $data): InventoryItemFile;

    public function nextPositionForInventoryItem(string $inventoryItemId): int;

    public function delete(InventoryItemFile $file): void;
}
