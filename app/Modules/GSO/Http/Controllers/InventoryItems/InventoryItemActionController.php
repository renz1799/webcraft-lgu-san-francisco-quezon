<?php

namespace App\Modules\GSO\Http\Controllers\InventoryItems;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\InventoryItems\DestroyInventoryItemRequest;
use App\Modules\GSO\Http\Requests\InventoryItems\RestoreInventoryItemRequest;
use App\Modules\GSO\Http\Requests\InventoryItems\StoreInventoryItemRequest;
use App\Modules\GSO\Http\Requests\InventoryItems\UpdateInventoryItemRequest;
use App\Modules\GSO\Services\Contracts\InventoryItemServiceInterface;
use Illuminate\Http\JsonResponse;

class InventoryItemActionController extends Controller
{
    public function __construct(
        private readonly InventoryItemServiceInterface $inventoryItems,
    ) {
        $this->middleware('permission:inventory_items.create|inventory_items.update|inventory_items.archive|inventory_items.restore|inventory_items.manage_files|inventory_items.manage_events|inventory_items.import_from_inspection')
            ->only(['show', 'store', 'update', 'destroy', 'restore']);
    }

    public function show(string $inventoryItem): JsonResponse
    {
        return response()->json([
            'data' => $this->inventoryItems->getForEdit($inventoryItem),
        ]);
    }

    public function store(StoreInventoryItemRequest $request): JsonResponse
    {
        $inventoryItem = $this->inventoryItems->create((string) $request->user()->id, $request->validated());

        return response()->json([
            'data' => $this->inventoryItems->getForEdit((string) $inventoryItem->id),
        ]);
    }

    public function update(UpdateInventoryItemRequest $request, string $inventoryItem): JsonResponse
    {
        $updated = $this->inventoryItems->update((string) $request->user()->id, $inventoryItem, $request->validated());

        return response()->json([
            'data' => $this->inventoryItems->getForEdit((string) $updated->id),
        ]);
    }

    public function destroy(DestroyInventoryItemRequest $request, string $inventoryItem): JsonResponse
    {
        $this->inventoryItems->delete((string) $request->user()->id, $inventoryItem);

        return response()->json(['ok' => true]);
    }

    public function restore(RestoreInventoryItemRequest $request, string $inventoryItem): JsonResponse
    {
        $this->inventoryItems->restore((string) $request->user()->id, $inventoryItem);

        return response()->json(['ok' => true]);
    }
}
