<?php

namespace App\Modules\GSO\Services\ITR;

use App\Modules\GSO\Models\ItrItem;
use App\Modules\GSO\Repositories\Contracts\ITR\ItrItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\ITR\ItrRepositoryInterface;
use App\Modules\GSO\Services\Contracts\ITR\ItrItemServiceInterface;
use Illuminate\Support\Facades\DB;

class ItrItemService implements ItrItemServiceInterface
{
    public function __construct(
        private readonly ItrRepositoryInterface $itrs,
        private readonly ItrItemRepositoryInterface $itrItems,
    ) {}

    public function suggestItems(string $itrId, string $query): array
    {
        $itr = $this->itrs->findOrFail($itrId);
        abort_if((string) $itr->status !== 'draft', 409, 'You can only add items while ITR is draft.');

        return $this->itrItems->suggestTransferableItems($itrId, $query, 12);
    }

    public function listForEdit(string $itrId): array
    {
        $this->itrs->findOrFail($itrId);

        return $this->itrItems->listByItrId($itrId)
            ->map(function (ItrItem $item) {
                return [
                    'id' => (string) $item->id,
                    'inventory_item_id' => (string) $item->inventory_item_id,
                    'line_no' => (int) ($item->line_no ?? 0),
                    'quantity' => (int) ($item->quantity ?? 1),
                    'inventory_item_no' => (string) ($item->inventory_item_no_snapshot ?? $item->inventoryItem?->property_number ?? $item->inventoryItem?->stock_number ?? ''),
                    'item_name' => (string) ($item->item_name_snapshot ?? $item->inventoryItem?->item?->item_name ?? ''),
                    'description' => (string) ($item->description_snapshot ?? ''),
                    'date_acquired' => $item->date_acquired_snapshot ? $item->date_acquired_snapshot->toDateString() : null,
                    'amount' => $item->amount_snapshot !== null ? (float) $item->amount_snapshot : null,
                    'estimated_useful_life' => (string) ($item->estimated_useful_life_snapshot ?? ''),
                    'condition' => (string) ($item->condition_snapshot ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    public function addItem(string $actorUserId, string $itrId, string $inventoryItemId): ItrItem
    {
        return DB::transaction(function () use ($actorUserId, $itrId, $inventoryItemId) {
            $itr = $this->itrs->findOrFail($itrId);
            abort_if((string) $itr->status !== 'draft', 409, 'You can only add items while ITR is draft.');

            $created = $this->itrItems->addInventoryItemToItr($itrId, $inventoryItemId);

            return $created->refresh();
        });
    }

    public function removeItem(string $actorUserId, string $itrId, string $itrItemId): void
    {
        DB::transaction(function () use ($actorUserId, $itrId, $itrItemId) {
            $itr = $this->itrs->findOrFail($itrId);
            abort_if((string) $itr->status !== 'draft', 409, 'You can only remove items while ITR is draft.');

            $item = $this->itrItems->findById($itrItemId);
            abort_if(!$item, 404, 'ITR item not found.');
            abort_unless((string) $item->itr_id === (string) $itrId, 404);

            $this->itrItems->delete($item);
        });
    }
}



