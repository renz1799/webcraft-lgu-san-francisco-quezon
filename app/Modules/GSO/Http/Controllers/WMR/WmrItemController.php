<?php

namespace App\Modules\GSO\Http\Controllers\WMR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\WMR\Items\AddWmrItemRequest;
use App\Modules\GSO\Http\Requests\WMR\Items\RemoveWmrItemRequest;
use App\Modules\GSO\Http\Requests\WMR\Items\SuggestWmrItemsRequest;
use App\Modules\GSO\Http\Requests\WMR\Items\UpdateWmrItemRequest;
use App\Modules\GSO\Models\Wmr;
use App\Modules\GSO\Models\WmrItem;
use App\Modules\GSO\Services\Contracts\WMR\WmrItemServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WmrItemController extends Controller
{
    public function __construct(
        private readonly WmrItemServiceInterface $wmrItems,
    ) {}

    public function suggest(SuggestWmrItemsRequest $request, Wmr $wmr): JsonResponse
    {
        $items = $this->wmrItems->suggestItems((string) $wmr->id, (string) ($request->validated()['q'] ?? ''));

        return response()->json(['items' => $items]);
    }

    public function list(Request $request, Wmr $wmr): JsonResponse
    {
        return response()->json([
            'items' => $this->wmrItems->listForEdit((string) $wmr->id),
        ]);
    }

    public function store(AddWmrItemRequest $request, Wmr $wmr): JsonResponse
    {
        $created = $this->wmrItems->addItem(
            actorUserId: (string) $request->user()->id,
            wmrId: (string) $wmr->id,
            inventoryItemId: (string) $request->validated()['inventory_item_id'],
        );

        return response()->json([
            'message' => 'Item added to WMR.',
            'data' => ['id' => (string) $created->id],
        ], 201);
    }

    public function update(UpdateWmrItemRequest $request, Wmr $wmr, WmrItem $wmrItem): JsonResponse
    {
        abort_unless((string) $wmrItem->wmr_id === (string) $wmr->id, 404);

        $updated = $this->wmrItems->updateItem(
            actorUserId: (string) $request->user()->id,
            wmrId: (string) $wmr->id,
            wmrItemId: (string) $wmrItem->id,
            payload: $request->validated(),
        );

        return response()->json([
            'message' => 'WMR disposal line updated.',
            'data' => ['id' => (string) $updated->id],
        ]);
    }

    public function destroy(RemoveWmrItemRequest $request, Wmr $wmr, WmrItem $wmrItem): JsonResponse
    {
        abort_unless((string) $wmrItem->wmr_id === (string) $wmr->id, 404);

        $this->wmrItems->removeItem(
            actorUserId: (string) $request->user()->id,
            wmrId: (string) $wmr->id,
            wmrItemId: (string) $wmrItem->id,
        );

        return response()->json([
            'message' => 'Item removed from WMR.',
        ]);
    }
}

