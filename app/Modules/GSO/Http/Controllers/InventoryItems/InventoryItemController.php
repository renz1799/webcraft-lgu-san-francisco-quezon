<?php

namespace App\Modules\GSO\Http\Controllers\InventoryItems;

use App\Core\Models\Department;
use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\InventoryItems\InventoryItemTableDataRequest;
use App\Modules\GSO\Models\AccountableOfficer;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\Inspection;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Services\Contracts\InventoryItemServiceInterface;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryEventTypes;
use App\Modules\GSO\Support\InventoryFileTypes;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function __construct(
        private readonly InventoryItemServiceInterface $inventoryItems,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view Inventory Items|modify Inventory Items')
            ->only(['index', 'data']);
    }

    public function index(): View
    {
        return view('gso::inventory-items.index', [
            'items' => Item::query()
                ->with(['asset' => fn ($query) => $query->select(['id', 'asset_code', 'asset_name'])])
                ->whereNull('deleted_at')
                ->where('tracking_type', 'property')
                ->orderBy('item_name')
                ->get(['id', 'asset_id', 'item_name', 'item_identification', 'base_unit', 'requires_serial']),
            'departments' => Department::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'fundSources' => FundSource::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'accountableOfficers' => AccountableOfficer::query()
                ->whereNull('deleted_at')
                ->orderBy('full_name')
                ->get(['id', 'full_name']),
            'inspections' => Inspection::query()
                ->whereNull('deleted_at')
                ->orderByDesc('created_at')
                ->get(['id', 'item_name', 'po_number', 'dv_number', 'status']),
            'inventoryStatuses' => InventoryStatuses::labels(),
            'inventoryConditions' => InventoryConditions::labels(),
            'inventoryCustodyStates' => InventoryCustodyStates::labels(),
            'inventoryEventTypes' => InventoryEventTypes::manualLabels(),
            'inventoryFileTypes' => InventoryFileTypes::labels(),
        ]);
    }

    public function data(InventoryItemTableDataRequest $request): JsonResponse
    {
        $payload = $this->inventoryItems->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }
}
