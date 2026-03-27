<?php

namespace App\Modules\GSO\Services\Contracts\PAR;

use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Models\ParItem;
use Illuminate\Support\Collection;

interface ParServiceInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array;
    
    public function createDraft(string $actorUserId, array $payload): Par;

    public function update(string $actorUserId, string $parId, array $payload): Par;

    public function addItem(string $actorUserId, Par $par, InventoryItem $item, int $quantity = 1): void;

    public function removeItem(string $actorUserId, Par $par, ParItem $parItem): void;

    public function getItems(Par $par): Collection;

    public function delete(string $actorUserId, string $parId): Par;

    public function restore(string $actorUserId, string $parId): Par;
}
