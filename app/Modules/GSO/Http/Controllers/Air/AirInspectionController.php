<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\FinalizeAirInspectionRequest;
use App\Modules\GSO\Http\Requests\Air\SaveAirInspectionRequest;
use App\Modules\GSO\Services\Contracts\AirInspectionServiceInterface;
use App\Modules\GSO\Support\InventoryConditions;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AirInspectionController extends Controller
{
    public function __construct(
        private readonly AirInspectionServiceInterface $inspection,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view AIR|modify AIR')
            ->only(['show']);

        $this->middleware('role_or_permission:Administrator|admin|modify AIR')
            ->only(['save', 'finalize']);
    }

    public function show(string $air): View
    {
        $payload = $this->inspection->getForInspection($air);

        return view('gso::air.inspect', [
            'air' => $payload['air'],
            'items' => $payload['items'],
            'conditionStatuses' => InventoryConditions::labels(),
        ]);
    }

    public function save(SaveAirInspectionRequest $request, string $air): JsonResponse
    {
        return response()->json([
            'data' => $this->inspection->saveInspection((string) $request->user()?->id, $air, $request->validated()),
            'message' => 'AIR inspection saved.',
        ]);
    }

    public function finalize(FinalizeAirInspectionRequest $request, string $air): JsonResponse
    {
        return response()->json([
            'data' => $this->inspection->finalizeInspection((string) $request->user()?->id, $air),
            'message' => 'AIR inspection finalized.',
        ]);
    }
}
