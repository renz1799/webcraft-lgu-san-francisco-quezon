<?php

namespace App\Modules\GSO\Http\Controllers\Inspections;

use App\Core\Models\Department;
use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Inspections\InspectionTableDataRequest;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Services\Contracts\InspectionServiceInterface;
use App\Modules\GSO\Support\InspectionStatuses;
use App\Modules\GSO\Support\InventoryConditions;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class InspectionController extends Controller
{
    public function __construct(
        private readonly InspectionServiceInterface $inspections,
    ) {
        $this->middleware('permission:inspections.view|inspections.create|inspections.update|inspections.archive|inspections.restore|inspections.manage_photos')
            ->only(['index', 'data']);
    }

    public function index(): View
    {
        return view('gso::inspections.index', [
            'items' => Item::query()
                ->whereNull('deleted_at')
                ->orderBy('item_name')
                ->get(['id', 'item_name', 'item_identification']),
            'departments' => Department::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'inspectionStatuses' => InspectionStatuses::labels(),
            'inventoryConditions' => InventoryConditions::labels(),
        ]);
    }

    public function data(InspectionTableDataRequest $request): JsonResponse
    {
        $payload = $this->inspections->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }
}
