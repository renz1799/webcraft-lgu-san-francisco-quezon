<?php

namespace App\Modules\GSO\Http\Controllers\Items;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Items\DestroyItemRequest;
use App\Modules\GSO\Http\Requests\Items\RestoreItemRequest;
use App\Modules\GSO\Http\Requests\Items\StoreItemRequest;
use App\Modules\GSO\Http\Requests\Items\UpdateItemRequest;
use App\Modules\GSO\Services\Contracts\ItemServiceInterface;
use Illuminate\Http\JsonResponse;

class ItemActionController extends Controller
{
    public function __construct(
        private readonly ItemServiceInterface $items,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|modify Items')
            ->only(['show', 'store', 'update', 'destroy', 'restore']);
    }

    public function show(string $item): JsonResponse
    {
        return response()->json([
            'data' => $this->items->getForEdit($item),
        ]);
    }

    public function store(StoreItemRequest $request): JsonResponse
    {
        $item = $this->items->create((string) $request->user()->id, $request->validated());

        return response()->json([
            'data' => $this->items->getForEdit((string) $item->id),
        ]);
    }

    public function update(UpdateItemRequest $request, string $item): JsonResponse
    {
        $updated = $this->items->update((string) $request->user()->id, $item, $request->validated());

        return response()->json([
            'data' => $this->items->getForEdit((string) $updated->id),
        ]);
    }

    public function destroy(DestroyItemRequest $request, string $item): JsonResponse
    {
        $this->items->delete((string) $request->user()->id, $item);

        return response()->json(['ok' => true]);
    }

    public function restore(RestoreItemRequest $request, string $item): JsonResponse
    {
        $this->items->restore((string) $request->user()->id, $item);

        return response()->json(['ok' => true]);
    }
}
