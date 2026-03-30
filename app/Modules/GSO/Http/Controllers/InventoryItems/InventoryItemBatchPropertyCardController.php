<?php

namespace App\Modules\GSO\Http\Controllers\InventoryItems;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\InventoryItems\PrintInventoryItemPropertyCardsRequest;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Repositories\Contracts\InventoryItemRepositoryInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemCardPrintServiceInterface;
use Illuminate\View\View;

class InventoryItemBatchPropertyCardController extends Controller
{
    public function __construct(
        private readonly InventoryItemRepositoryInterface $inventoryItems,
        private readonly InventoryItemCardPrintServiceInterface $printer,
    ) {
        $this->middleware('permission:reports.property_cards.view|inventory_items.view|inventory_items.update');
    }

    public function print(PrintInventoryItemPropertyCardsRequest $request): View
    {
        $validated = $request->validated();

        $page = max(1, (int) ($validated['page'] ?? 1));
        $size = max(1, min((int) ($validated['size'] ?? 15), 50));

        $filters = $validated;
        unset($filters['page'], $filters['size'], $filters['preview']);

        $paginator = $this->inventoryItems->paginateForTable($filters, $page, $size);

        $cards = $paginator->getCollection()
            ->map(function (InventoryItem $inventoryItem): array {
                $payload = $this->printer->getPropertyCardPrintPayload($inventoryItem, [
                    'preview' => true,
                ]);

                return [
                    'view' => $payload['view'],
                    'data' => $payload['data'],
                    'inventory_item_id' => (string) $inventoryItem->id,
                ];
            })
            ->values()
            ->all();

        return view('gso::property-cards.batch-print', [
            'cards' => $cards,
            'isPreview' => $request->boolean('preview', true),
            'page' => $page,
            'size' => $size,
            'total' => $paginator->total(),
        ]);
    }
}
