<?php

namespace App\Modules\GSO\Http\Controllers\PAR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\PAR\DestroyParItemRequest;
use App\Modules\GSO\Http\Requests\PAR\StoreParItemRequest;
use App\Modules\GSO\Http\Requests\PAR\SuggestParItemRequest;
use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Models\ParItem;
use App\Modules\GSO\Services\Contracts\PAR\ParItemServiceInterface;
use App\Modules\GSO\Services\Contracts\PAR\ParServiceInterface;
use Illuminate\Http\JsonResponse;

class ParItemController extends Controller
{
    public function __construct(
        private readonly ParItemServiceInterface $parItems,
        private readonly ParServiceInterface $pars,
    ) {
    }

    public function suggest(SuggestParItemRequest $request, Par $par): JsonResponse
    {
        $validated = $request->validated();
        $rows = $this->parItems->suggestItems((string) $par->id, (string) ($validated['q'] ?? ''));

        return response()->json([
            'ok' => true,
            'data' => $rows,
        ]);
    }

    public function store(StoreParItemRequest $request, Par $par): JsonResponse
    {
        $validated = $request->validated();

        $parItem = $this->parItems->addItem(
            actorUserId: (string) $request->user()?->id,
            parId: (string) $par->id,
            inventoryItemId: (string) $validated['inventory_item_id'],
            quantity: (int) ($validated['quantity'] ?? 1),
        );

        $parItem->loadMissing(['inventoryItem.item']);

        $inventoryItem = $parItem->inventoryItem;
        $quantity = (int) ($parItem->quantity ?? 1);
        $unitCost = is_null($parItem->amount_snapshot) ? null : (float) $parItem->amount_snapshot;
        $totalCost = is_null($unitCost) ? null : ($quantity * $unitCost);

        return response()->json([
            'ok' => true,
            'message' => 'Item added to PAR.',
            'par_item_id' => (string) $parItem->id,
            'item' => [
                'id' => (string) $parItem->id,
                'property_number' => (string) ($parItem->property_number_snapshot ?? $inventoryItem?->property_number ?? ''),
                'item_name' => (string) ($parItem->item_name_snapshot ?? $inventoryItem?->item?->item_name ?? ''),
                'description' => (string) ($inventoryItem?->description ?? ''),
                'unit' => (string) ($parItem->unit_snapshot ?? $inventoryItem?->unit ?? ''),
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'amount' => $unitCost,
                'delete_url' => route('gso.pars.items.destroy', [
                    'par' => (string) $par->id,
                    'parItem' => (string) $parItem->id,
                ]),
            ],
        ]);
    }

    public function destroy(DestroyParItemRequest $request, Par $par, ParItem $parItem): JsonResponse
    {
        abort_unless((string) $parItem->par_id === (string) $par->id, 404);

        $this->pars->removeItem(
            actorUserId: (string) $request->user()?->id,
            par: $par,
            parItem: $parItem,
        );

        return response()->json([
            'ok' => true,
            'message' => 'Item removed from PAR.',
            'par_item_id' => (string) $parItem->id,
        ]);
    }
}
