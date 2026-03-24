<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\FinalizeAirInspectionRequest;
use App\Modules\GSO\Http\Requests\Air\ReopenAirInspectionRequest;
use App\Modules\GSO\Http\Requests\Air\SaveAirInspectionRequest;
use App\Modules\GSO\Services\Contracts\Air\AirServiceInterface;
use App\Modules\GSO\Services\Contracts\Air\AirInspectionServiceInterface;
use App\Modules\GSO\Support\InventoryConditions;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AirInspectionController extends Controller
{
    public function __construct(
        private readonly AirInspectionServiceInterface $inspection,
        private readonly AirServiceInterface $airs,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view AIR|modify AIR')
            ->only(['show']);

        $this->middleware('role_or_permission:Administrator|admin|modify AIR')
            ->only(['save', 'finalize']);

        $this->middleware('role_or_permission:Administrator|admin|modify Inspection Status')
            ->only(['reopen']);
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

    public function reopen(ReopenAirInspectionRequest $request, string $air): JsonResponse
    {
        $updated = $this->airs->reopenInspection(
            (string) $request->user()?->id,
            $air,
            $request->validated('reason'),
        );

        return response()->json([
            'data' => $this->inspection->getForInspection((string) $updated->id),
            'message' => 'AIR inspection reopened to Submitted.',
        ]);
    }
}
