<?php

namespace App\Modules\GSO\Http\Controllers\RIS;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\RIS\Items\AddRisItemRequest;
use App\Modules\GSO\Http\Requests\RIS\Items\BulkUpdateRisItemsRequest;
use App\Modules\GSO\Http\Requests\RIS\Items\RemoveRisItemRequest;
use App\Modules\GSO\Http\Requests\RIS\Items\SuggestRisItemsRequest;
use App\Modules\GSO\Http\Requests\RIS\Items\UpdateRisItemRequest;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Models\RisItem;
use App\Modules\GSO\Services\Contracts\RIS\RisItemServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RisItemController extends Controller
{
    public function __construct(
        private readonly RisItemServiceInterface $risItems,
    ) {
    }

    public function suggest(SuggestRisItemsRequest $request, Ris $ris): JsonResponse
    {
        $query = (string) ($request->validated()['q'] ?? '');

        return response()->json([
            'items' => $this->risItems->suggestConsumables(
                actorUserId: (string) $request->user()?->id,
                risId: (string) $ris->id,
                query: $query,
            ),
        ]);
    }

    public function list(Request $request, Ris $ris): JsonResponse
    {
        return response()->json([
            'items' => $this->risItems->listForEdit((string) $ris->id),
        ]);
    }

    public function add(AddRisItemRequest $request, Ris $ris): JsonResponse
    {
        $validated = $request->validated();

        $created = $this->risItems->addItem(
            actorUserId: (string) $request->user()?->id,
            risId: (string) $ris->id,
            itemId: (string) $validated['item_id'],
            qtyRequested: isset($validated['qty_requested']) ? (int) $validated['qty_requested'] : null,
            remarks: $validated['remarks'] ?? null,
            fundSourceId: $validated['fund_source_id'] ?? null,
        );

        return response()->json([
            'message' => 'Item added.',
            'data' => [
                'id' => (string) $created->id,
            ],
        ], 201);
    }

    public function update(UpdateRisItemRequest $request, Ris $ris, RisItem $risItem): JsonResponse
    {
        abort_unless((string) $risItem->ris_id === (string) $ris->id, 404);

        $updated = $this->risItems->updateItem(
            actorUserId: (string) $request->user()?->id,
            risId: (string) $ris->id,
            risItemId: (string) $risItem->id,
            data: $request->validated(),
        );

        return response()->json([
            'message' => 'Item updated.',
            'data' => [
                'id' => (string) $updated->id,
            ],
        ]);
    }

    public function bulkUpdate(BulkUpdateRisItemsRequest $request, Ris $ris): JsonResponse
    {
        $result = $this->risItems->bulkUpdate(
            actorUserId: (string) $request->user()?->id,
            risId: (string) $ris->id,
            items: $request->validated()['items'],
        );

        return response()->json([
            'message' => 'Items updated.',
            'data' => $result,
        ]);
    }

    public function remove(RemoveRisItemRequest $request, Ris $ris, RisItem $risItem): JsonResponse
    {
        abort_unless((string) $risItem->ris_id === (string) $ris->id, 404);

        $this->risItems->removeItem(
            actorUserId: (string) $request->user()?->id,
            risId: (string) $ris->id,
            risItemId: (string) $risItem->id,
        );

        return response()->json([
            'message' => 'Item removed.',
        ]);
    }
}
