<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\FinalizeAirInspectionRequest;
use App\Modules\GSO\Http\Requests\Air\ReopenAirInspectionRequest;
use App\Modules\GSO\Http\Requests\Air\SaveAirInspectionRequest;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Services\Contracts\Air\AirServiceInterface;
use App\Modules\GSO\Services\Contracts\Air\AirInspectionServiceInterface;
use App\Modules\GSO\Support\InventoryConditions;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AirInspectionController extends Controller
{
    public function __construct(
        private readonly AirInspectionServiceInterface $inspection,
        private readonly AirServiceInterface $airs,
    ) {
        $this->middleware('permission:air.view|air.inspect|air.finalize_inspection|air.reopen_inspection')
            ->only(['show']);

        $this->middleware('permission:air.inspect|air.finalize_inspection')
            ->only(['save', 'finalize']);

        $this->middleware('permission:air.reopen_inspection')
            ->only(['reopen']);
    }

    public function show(string $air): View
    {
        $payload = $this->inspection->getForInspection($air);
        $existingRis = Ris::query()
            ->where('air_id', $air)
            ->first();
        $hasConsumableItems = DB::table('air_items')
            ->where('air_id', $air)
            ->where('qty_accepted', '>', 0)
            ->whereRaw("LOWER(TRIM(COALESCE(tracking_type_snapshot, ''))) IN ('consumable', 'consumables')")
            ->exists();

        return view('gso::air.inspect', [
            'air' => $payload['air'],
            'items' => $payload['items'],
            'conditionStatuses' => InventoryConditions::labels(),
            'existingRis' => $existingRis,
            'hasConsumableItems' => $hasConsumableItems,
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
