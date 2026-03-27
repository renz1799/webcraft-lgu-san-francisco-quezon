<?php

namespace App\Modules\GSO\Http\Controllers\ICS;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\ICS\Items\AddIcsItemRequest;
use App\Modules\GSO\Http\Requests\ICS\Items\RemoveIcsItemRequest;
use App\Modules\GSO\Http\Requests\ICS\Items\SuggestIcsItemsRequest;
use App\Modules\GSO\Models\Ics;
use App\Modules\GSO\Models\IcsItem;
use App\Modules\GSO\Services\Contracts\ICS\IcsItemServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IcsItemController extends Controller
{
    public function __construct(
        private readonly IcsItemServiceInterface $icsItems,
    ) {}

    public function suggest(SuggestIcsItemsRequest $request, Ics $ics): JsonResponse
    {
        $items = $this->icsItems->suggestItems((string) $ics->id, (string) ($request->validated()['q'] ?? ''));

        return response()->json(['items' => $items]);
    }

    public function list(Request $request, Ics $ics): JsonResponse
    {
        return response()->json([
            'items' => $this->icsItems->listForEdit((string) $ics->id),
        ]);
    }

    public function store(AddIcsItemRequest $request, Ics $ics): JsonResponse
    {
        $created = $this->icsItems->addItem(
            actorUserId: (string) $request->user()?->id,
            icsId: (string) $ics->id,
            inventoryItemId: (string) $request->validated()['inventory_item_id'],
        );

        return response()->json([
            'message' => 'Item added to ICS.',
            'data' => ['id' => (string) $created->id],
        ], 201);
    }

    public function destroy(RemoveIcsItemRequest $request, Ics $ics, IcsItem $icsItem): JsonResponse
    {
        abort_unless((string) $icsItem->ics_id === (string) $ics->id, 404);

        $this->icsItems->removeItem(
            actorUserId: (string) $request->user()?->id,
            icsId: (string) $ics->id,
            icsItemId: (string) $icsItem->id,
        );

        return response()->json([
            'message' => 'Item removed from ICS.',
        ]);
    }
}
