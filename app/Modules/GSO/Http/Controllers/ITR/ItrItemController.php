<?php

namespace App\Modules\GSO\Http\Controllers\ITR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\ITR\Items\AddItrItemRequest;
use App\Modules\GSO\Http\Requests\ITR\Items\RemoveItrItemRequest;
use App\Modules\GSO\Http\Requests\ITR\Items\SuggestItrItemsRequest;
use App\Modules\GSO\Models\Itr;
use App\Modules\GSO\Models\ItrItem;
use App\Modules\GSO\Services\Contracts\ITR\ItrItemServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItrItemController extends Controller
{
    public function __construct(
        private readonly ItrItemServiceInterface $itrItems,
    ) {}

    public function suggest(SuggestItrItemsRequest $request, Itr $itr): JsonResponse
    {
        $items = $this->itrItems->suggestItems((string) $itr->id, (string) ($request->validated()['q'] ?? ''));

        return response()->json(['items' => $items]);
    }

    public function list(Request $request, Itr $itr): JsonResponse
    {
        return response()->json([
            'items' => $this->itrItems->listForEdit((string) $itr->id),
        ]);
    }

    public function store(AddItrItemRequest $request, Itr $itr): JsonResponse
    {
        $created = $this->itrItems->addItem(
            actorUserId: (string) $request->user()->id,
            itrId: (string) $itr->id,
            inventoryItemId: (string) $request->validated()['inventory_item_id'],
        );

        return response()->json([
            'message' => 'Item added to ITR.',
            'data' => ['id' => (string) $created->id],
        ], 201);
    }

    public function destroy(RemoveItrItemRequest $request, Itr $itr, ItrItem $itrItem): JsonResponse
    {
        abort_unless((string) $itrItem->itr_id === (string) $itr->id, 404);

        $this->itrItems->removeItem(
            actorUserId: (string) $request->user()->id,
            itrId: (string) $itr->id,
            itrItemId: (string) $itrItem->id,
        );

        return response()->json([
            'message' => 'Item removed from ITR.',
        ]);
    }
}



