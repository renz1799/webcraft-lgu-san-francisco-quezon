<?php

namespace App\Modules\GSO\Http\Controllers\InventoryItems;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Repositories\Contracts\InventoryItemRepositoryInterface;
use Illuminate\Http\RedirectResponse;

class InventoryItemPropertyCardController extends Controller
{
    public function __construct(
        private readonly InventoryItemRepositoryInterface $inventoryItems,
    ) {
        $this->middleware('permission:reports.property_cards.view|inventory_items.view|inventory_items.update');
    }

    public function print(string $inventoryItem): RedirectResponse
    {
        $resolvedInventoryItem = $this->inventoryItems->findOrFail($inventoryItem, true);

        $params = array_filter([
            'preview' => request()->boolean('preview'),
            'inventory_item_id' => (string) $resolvedInventoryItem->id,
            'department_id' => $resolvedInventoryItem->department_id ? (string) $resolvedInventoryItem->department_id : null,
            'item_id' => $resolvedInventoryItem->item_id ? (string) $resolvedInventoryItem->item_id : null,
            'fund_source_id' => $resolvedInventoryItem->fund_source_id ? (string) $resolvedInventoryItem->fund_source_id : null,
            'classification' => $resolvedInventoryItem->is_ics ? 'ics' : 'ppe',
            'custody_state' => $resolvedInventoryItem->custody_state ?: null,
            'inventory_status' => $resolvedInventoryItem->status ?: null,
            'page' => 1,
            'size' => 1,
        ], static fn ($value) => $value !== null && $value !== '');

        return redirect()->route('gso.reports.property-cards.print', $params);
    }
}
