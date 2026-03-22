<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\DestroyAirInspectionUnitRequest;
use App\Modules\GSO\Http\Requests\Air\SaveAirInspectionUnitsRequest;
use App\Modules\GSO\Services\Contracts\AirInspectionUnitServiceInterface;
use Illuminate\Http\JsonResponse;

class AirInspectionUnitController extends Controller
{
    public function __construct(
        private readonly AirInspectionUnitServiceInterface $units,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view AIR|modify AIR')
            ->only(['index']);

        $this->middleware('role_or_permission:Administrator|admin|modify AIR')
            ->only(['save', 'destroy']);
    }

    public function index(string $air, string $airItem): JsonResponse
    {
        return response()->json([
            'data' => $this->units->listForAirItem($air, $airItem),
        ]);
    }

    public function save(SaveAirInspectionUnitsRequest $request, string $air, string $airItem): JsonResponse
    {
        return response()->json([
            'data' => $this->units->saveForAirItem(
                (string) $request->user()?->id,
                $air,
                $airItem,
                $request->validated('units', []),
            ),
            'message' => 'Inspection unit rows saved.',
        ]);
    }

    public function destroy(DestroyAirInspectionUnitRequest $request, string $air, string $airItem, string $unit): JsonResponse
    {
        return response()->json([
            'data' => $this->units->deleteUnit((string) $request->user()?->id, $air, $airItem, $unit),
            'message' => 'Inspection unit row removed.',
        ]);
    }
}
