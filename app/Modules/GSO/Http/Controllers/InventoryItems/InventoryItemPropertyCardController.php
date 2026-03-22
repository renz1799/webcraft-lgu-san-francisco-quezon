<?php

namespace App\Modules\GSO\Http\Controllers\InventoryItems;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Repositories\Contracts\InventoryItemRepositoryInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemCardPrintServiceInterface;
use Illuminate\View\View;

class InventoryItemPropertyCardController extends Controller
{
    public function __construct(
        private readonly InventoryItemRepositoryInterface $inventoryItems,
        private readonly InventoryItemCardPrintServiceInterface $printer,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view Inventory Items|modify Inventory Items');
    }

    public function print(string $inventoryItem): View
    {
        $resolvedInventoryItem = $this->inventoryItems->findOrFail($inventoryItem, true);
        $payload = $this->printer->getPropertyCardPrintPayload($resolvedInventoryItem, [
            'preview' => request()->boolean('preview'),
        ]);

        return view($payload['view'], $payload['data']);
    }
}
