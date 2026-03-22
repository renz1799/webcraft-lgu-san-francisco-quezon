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
        $this->middleware('role_or_permission:Administrator|admin|view Inventory Items|modify Inventory Items')
            ->only(['index']);

        $this->middleware('role_or_permission:Administrator|admin|modify Inventory Items')
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
