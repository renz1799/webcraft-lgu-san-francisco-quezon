<?php

namespace App\Modules\GSO\Services\Contracts;

use App\Modules\GSO\Models\AirItem;

interface AirItemServiceInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listForAir(string $airId): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function suggestItems(string $airId, string $query, int $limit = 10): array;

    public function addItemToDraft(string $actorUserId, string $airId, array $data): AirItem;

    public function updateItemInDraft(string $actorUserId, string $airId, string $airItemId, array $data): AirItem;

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function bulkUpdateItemsInDraft(string $actorUserId, string $airId, array $items): void;

    public function removeItemFromDraft(string $actorUserId, string $airId, string $airItemId): void;
}
