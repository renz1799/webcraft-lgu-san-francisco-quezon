<?php

namespace App\Modules\GSO\Services\Contracts;

use App\Modules\GSO\Models\InventoryItem;

interface InventoryItemServiceInterface
{
    public function datatable(array $params): array;

    public function getForEdit(string $inventoryItemId): array;

    public function create(string $actorUserId, array $data): InventoryItem;

    public function update(string $actorUserId, string $inventoryItemId, array $data): InventoryItem;

    public function delete(string $actorUserId, string $inventoryItemId): void;

    public function restore(string $actorUserId, string $inventoryItemId): void;
}
