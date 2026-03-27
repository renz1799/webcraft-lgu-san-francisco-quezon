<?php

namespace App\Modules\GSO\Http\Controllers\PTR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\PTR\Items\AddPtrItemRequest;
use App\Modules\GSO\Http\Requests\PTR\Items\RemovePtrItemRequest;
use App\Modules\GSO\Http\Requests\PTR\Items\SuggestPtrItemsRequest;
use App\Modules\GSO\Models\Ptr;
use App\Modules\GSO\Models\PtrItem;
use App\Modules\GSO\Services\Contracts\PTR\PtrItemServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PtrItemController extends Controller
{
    public function __construct(
        private readonly PtrItemServiceInterface $ptrItems,
    ) {}

    public function suggest(SuggestPtrItemsRequest $request, Ptr $ptr): JsonResponse
    {
        $items = $this->ptrItems->suggestItems((string) $ptr->id, (string) ($request->validated()['q'] ?? ''));

        return response()->json(['items' => $items]);
    }

    public function list(Request $request, Ptr $ptr): JsonResponse
    {
        return response()->json([
            'items' => $this->ptrItems->listForEdit((string) $ptr->id),
        ]);
    }

    public function store(AddPtrItemRequest $request, Ptr $ptr): JsonResponse
    {
        $created = $this->ptrItems->addItem(
            actorUserId: (string) $request->user()->id,
            ptrId: (string) $ptr->id,
            inventoryItemId: (string) $request->validated()['inventory_item_id'],
        );

        return response()->json([
            'message' => 'Item added to PTR.',
            'data' => ['id' => (string) $created->id],
        ], 201);
    }

    public function destroy(RemovePtrItemRequest $request, Ptr $ptr, PtrItem $ptrItem): JsonResponse
    {
        abort_unless((string) $ptrItem->ptr_id === (string) $ptr->id, 404);

        $this->ptrItems->removeItem(
            actorUserId: (string) $request->user()->id,
            ptrId: (string) $ptr->id,
            ptrItemId: (string) $ptrItem->id,
        );

        return response()->json([
            'message' => 'Item removed from PTR.',
        ]);
    }
}
