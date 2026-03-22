<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\InventoryItemFile;
use App\Modules\GSO\Repositories\Contracts\InventoryItemFileRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentInventoryItemFileRepository implements InventoryItemFileRepositoryInterface
{
    public function listForInventoryItem(string $inventoryItemId, bool $withTrashed = false): Collection
    {
        $query = InventoryItemFile::query()
            ->where('inventory_item_id', $inventoryItemId)
            ->orderByDesc('is_primary')
            ->orderBy('position')
            ->orderByDesc('created_at');

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->get();
    }

    public function findForInventoryItemOrFail(string $inventoryItemId, string $fileId, bool $withTrashed = false): InventoryItemFile
    {
        $query = InventoryItemFile::query()
            ->where('inventory_item_id', $inventoryItemId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($fileId);
    }

    public function create(array $data): InventoryItemFile
    {
        return InventoryItemFile::query()->create($data);
    }

    public function nextPositionForInventoryItem(string $inventoryItemId): int
    {
        return (int) InventoryItemFile::query()
            ->where('inventory_item_id', $inventoryItemId)
            ->max('position') + 1;
    }

    public function delete(InventoryItemFile $file): void
    {
        $file->delete();
    }
}
