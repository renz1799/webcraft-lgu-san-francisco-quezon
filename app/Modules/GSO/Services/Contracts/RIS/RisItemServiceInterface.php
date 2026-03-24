<?php

namespace App\Modules\GSO\Services\Contracts\RIS;

use App\Modules\GSO\Models\RisItem;

interface RisItemServiceInterface
{
    public function suggestConsumables(string $actorUserId, string $risId, string $query = ''): array;

    public function listForEdit(string $risId): array;

    public function addItem(
        string $actorUserId,
        string $risId,
        string $itemId,
        ?int $qtyRequested = null,
        ?string $remarks = null,
        ?string $fundSourceId = null,
    ): RisItem;

    public function updateItem(string $actorUserId, string $risId, string $risItemId, array $data): RisItem;

    public function bulkUpdate(string $actorUserId, string $risId, array $items): array;

    public function removeItem(string $actorUserId, string $risId, string $risItemId): void;
}