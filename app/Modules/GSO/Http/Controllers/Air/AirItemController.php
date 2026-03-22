<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\BulkUpdateAirItemsRequest;
use App\Modules\GSO\Http\Requests\Air\StoreAirItemRequest;
use App\Modules\GSO\Http\Requests\Air\SuggestAirItemRequest;
use App\Modules\GSO\Http\Requests\Air\UpdateAirItemRequest;
use App\Modules\GSO\Services\Contracts\AirItemServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AirItemController extends Controller
{
    public function __construct(
        private readonly AirItemServiceInterface $airItems,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view AIR|modify AIR')
            ->only(['index', 'suggest']);

        $this->middleware('role_or_permission:Administrator|admin|modify AIR')
            ->only(['store', 'update', 'bulkUpdate', 'destroy']);
    }

    public function index(string $air): JsonResponse
    {
        return response()->json([
            'data' => $this->airItems->listForAir($air),
        ]);
    }

    public function suggest(SuggestAirItemRequest $request, string $air): JsonResponse
    {
        return response()->json([
            'data' => $this->airItems->suggestItems($air, (string) $request->query('q', '')),
        ]);
    }

    public function store(StoreAirItemRequest $request, string $air): JsonResponse
    {
        $created = $this->airItems->addItemToDraft((string) $request->user()?->id, $air, $request->validated());

        return response()->json([
            'data' => $this->airItems->listForAir($air),
            'item' => ['id' => (string) $created->id],
            'message' => 'AIR item added.',
        ]);
    }

    public function update(UpdateAirItemRequest $request, string $air, string $airItem): JsonResponse
    {
        $updated = $this->airItems->updateItemInDraft((string) $request->user()?->id, $air, $airItem, $request->validated());

        return response()->json([
            'data' => $this->airItems->listForAir($air),
            'item' => ['id' => (string) $updated->id],
            'message' => 'AIR item updated.',
        ]);
    }

    public function bulkUpdate(BulkUpdateAirItemsRequest $request, string $air): JsonResponse
    {
        $this->airItems->bulkUpdateItemsInDraft((string) $request->user()?->id, $air, $request->validated('items'));

        return response()->json([
            'data' => $this->airItems->listForAir($air),
            'message' => 'AIR items saved.',
        ]);
    }

    public function destroy(Request $request, string $air, string $airItem): JsonResponse
    {
        $this->airItems->removeItemFromDraft((string) $request->user()?->id, $air, $airItem);

        return response()->json([
            'data' => $this->airItems->listForAir($air),
            'message' => 'AIR item removed.',
        ]);
    }
}
