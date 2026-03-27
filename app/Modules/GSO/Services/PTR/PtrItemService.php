<?php

namespace App\Modules\GSO\Services\PTR;

use App\Modules\GSO\Models\PtrItem;
use App\Modules\GSO\Repositories\Contracts\PTR\PtrItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\PTR\PtrRepositoryInterface;
use App\Modules\GSO\Services\Contracts\PTR\PtrItemServiceInterface;
use Illuminate\Support\Facades\DB;

class PtrItemService implements PtrItemServiceInterface
{
    public function __construct(
        private readonly PtrRepositoryInterface $ptrs,
        private readonly PtrItemRepositoryInterface $ptrItems,
    ) {}

    public function suggestItems(string $ptrId, string $query): array
    {
        $ptr = $this->ptrs->findOrFail($ptrId);
        abort_if((string) $ptr->status !== 'draft', 409, 'You can only add items while PTR is draft.');

        return $this->ptrItems->suggestTransferableItems($ptrId, $query, 12);
    }

    public function listForEdit(string $ptrId): array
    {
        $this->ptrs->findOrFail($ptrId);

        return $this->ptrItems->listByPtrId($ptrId)
            ->map(function (PtrItem $item) {
                return [
                    'id' => (string) $item->id,
                    'inventory_item_id' => (string) $item->inventory_item_id,
                    'line_no' => (int) ($item->line_no ?? 0),
                    'property_number' => (string) ($item->property_number_snapshot ?? $item->inventoryItem?->property_number ?? ''),
                    'item_name' => (string) ($item->item_name_snapshot ?? $item->inventoryItem?->item?->item_name ?? ''),
                    'description' => (string) ($item->description_snapshot ?? ''),
                    'date_acquired' => $item->date_acquired_snapshot ? $item->date_acquired_snapshot->toDateString() : null,
                    'amount' => $item->amount_snapshot !== null ? (float) $item->amount_snapshot : null,
                    'condition' => (string) ($item->condition_snapshot ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    public function addItem(string $actorUserId, string $ptrId, string $inventoryItemId): PtrItem
    {
        return DB::transaction(function () use ($actorUserId, $ptrId, $inventoryItemId) {
            $ptr = $this->ptrs->findOrFail($ptrId);
            abort_if((string) $ptr->status !== 'draft', 409, 'You can only add items while PTR is draft.');

            $created = $this->ptrItems->addInventoryItemToPtr($ptrId, $inventoryItemId);

            return $created->refresh();
        });
    }

    public function removeItem(string $actorUserId, string $ptrId, string $ptrItemId): void
    {
        DB::transaction(function () use ($actorUserId, $ptrId, $ptrItemId) {
            $ptr = $this->ptrs->findOrFail($ptrId);
            abort_if((string) $ptr->status !== 'draft', 409, 'You can only remove items while PTR is draft.');

            $item = $this->ptrItems->findById($ptrItemId);
            abort_if(!$item, 404, 'PTR item not found.');
            abort_unless((string) $item->ptr_id === (string) $ptrId, 404);

            $this->ptrItems->delete($item);
        });
    }
}

