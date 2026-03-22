<?php

namespace App\Modules\GSO\Services\Contracts;

use App\Modules\GSO\Models\Item;

interface ItemServiceInterface
{
    public function datatable(array $params): array;

    /**
     * @return array<string, mixed>
     */
    public function getForEdit(string $itemId): array;

    public function create(string $actorUserId, array $data): Item;

    public function update(string $actorUserId, string $itemId, array $data): Item;

    public function delete(string $actorUserId, string $itemId): void;

    public function restore(string $actorUserId, string $itemId): void;
}
