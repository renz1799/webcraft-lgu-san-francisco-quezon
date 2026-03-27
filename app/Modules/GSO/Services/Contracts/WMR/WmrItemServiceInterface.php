<?php

namespace App\Modules\GSO\Services\Contracts\WMR;

use App\Modules\GSO\Models\WmrItem;

interface WmrItemServiceInterface
{
    public function suggestItems(string $wmrId, string $query): array;

    public function listForEdit(string $wmrId): array;

    public function addItem(string $actorUserId, string $wmrId, string $inventoryItemId): WmrItem;

    public function updateItem(string $actorUserId, string $wmrId, string $wmrItemId, array $payload): WmrItem;

    public function removeItem(string $actorUserId, string $wmrId, string $wmrItemId): void;
}

