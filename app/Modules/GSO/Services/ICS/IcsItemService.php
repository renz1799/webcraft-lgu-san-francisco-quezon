<?php

namespace App\Modules\GSO\Services\ICS;

use App\Modules\GSO\Models\IcsItem;
use App\Modules\GSO\Repositories\Contracts\ICS\IcsItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\ICS\IcsRepositoryInterface;
use App\Modules\GSO\Services\Contracts\ICS\IcsItemServiceInterface;
use Illuminate\Support\Facades\DB;

class IcsItemService implements IcsItemServiceInterface
{
    public function __construct(
        private readonly IcsRepositoryInterface $icsRepo,
        private readonly IcsItemRepositoryInterface $icsItems,
    ) {}

    public function suggestItems(string $icsId, string $query): array
    {
        $ics = $this->icsRepo->findOrFail($icsId);
        abort_if((string) $ics->status !== 'draft', 409, 'You can only add items while ICS is draft.');

        return $this->icsItems->suggestFromGsoPool($icsId, $query, 12);
    }

    public function listForEdit(string $icsId): array
    {
        $this->icsRepo->findOrFail($icsId);

        return $this->icsItems->listByIcsId($icsId)
            ->map(function (IcsItem $item): array {
                return [
                    'id' => (string) $item->id,
                    'inventory_item_id' => (string) $item->inventory_item_id,
                    'line_no' => (int) ($item->line_no ?? 0),
                    'inventory_item_no' => (string) ($item->inventory_item_no_snapshot ?? ''),
                    'item_name' => (string) ($item->item_name_snapshot ?? $item->inventoryItem?->item?->item_name ?? ''),
                    'description' => (string) ($item->description_snapshot ?? ''),
                    'quantity' => (int) ($item->quantity ?? 1),
                    'unit' => (string) ($item->unit_snapshot ?? ''),
                    'unit_cost' => $item->unit_cost_snapshot !== null ? (float) $item->unit_cost_snapshot : null,
                    'total_cost' => $item->total_cost_snapshot !== null ? (float) $item->total_cost_snapshot : null,
                    'estimated_useful_life' => (string) ($item->estimated_useful_life_snapshot ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    public function addItem(string $actorUserId, string $icsId, string $inventoryItemId): IcsItem
    {
        return DB::transaction(function () use ($icsId, $inventoryItemId) {
            $ics = $this->icsRepo->findOrFail($icsId);
            abort_if((string) $ics->status !== 'draft', 409, 'You can only add items while ICS is draft.');

            return $this->icsItems->addInventoryItemToIcs($icsId, $inventoryItemId)->refresh();
        });
    }

    public function removeItem(string $actorUserId, string $icsId, string $icsItemId): void
    {
        DB::transaction(function () use ($icsId, $icsItemId) {
            $ics = $this->icsRepo->findOrFail($icsId);
            abort_if((string) $ics->status !== 'draft', 409, 'You can only remove items while ICS is draft.');

            $item = $this->icsItems->findById($icsItemId);
            abort_if(! $item, 404, 'ICS item not found.');
            abort_unless((string) $item->ics_id === (string) $icsId, 404);

            $this->icsItems->delete($item);
        });
    }
}
