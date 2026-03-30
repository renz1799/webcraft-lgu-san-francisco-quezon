<?php

namespace App\Modules\GSO\Http\Controllers\InventoryItems;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\InventoryItems\StoreInventoryItemEventRequest;
use App\Modules\GSO\Services\Contracts\InventoryItemEventServiceInterface;
use Illuminate\Http\JsonResponse;

class InventoryItemEventController extends Controller
{
    public function __construct(
        private readonly InventoryItemEventServiceInterface $events,
    ) {
        $this->middleware('permission:inventory_items.view|inventory_items.create|inventory_items.update|inventory_items.archive|inventory_items.restore|inventory_items.manage_files|inventory_items.manage_events|inventory_items.import_from_inspection')
            ->only(['index']);

        $this->middleware('permission:inventory_items.create|inventory_items.update|inventory_items.archive|inventory_items.restore|inventory_items.manage_files|inventory_items.manage_events|inventory_items.import_from_inspection')
            ->only(['store']);
    }

    public function index(string $inventoryItem): JsonResponse
    {
        return response()->json([
            'data' => $this->events->listForInventoryItem($inventoryItem),
        ]);
    }

    public function store(StoreInventoryItemEventRequest $request, string $inventoryItem): JsonResponse
    {
        $this->events->create((string) $request->user()->id, $inventoryItem, $request->validated());

        return response()->json([
            'data' => $this->events->listForInventoryItem($inventoryItem),
        ]);
    }
}
