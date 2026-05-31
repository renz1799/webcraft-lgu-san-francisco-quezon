<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\SaveAirInspectionItemComponentsRequest;
use App\Modules\GSO\Services\Air\AirInspectionItemComponentService;
use App\Modules\GSO\Services\Contracts\Air\AirInspectionServiceInterface;
use Illuminate\Http\JsonResponse;

class AirInspectionItemComponentController extends Controller
{
    public function __construct(
        private readonly AirInspectionItemComponentService $components,
        private readonly AirInspectionServiceInterface $inspection,
    ) {
        $this->middleware('permission:air.create|air.update|air.inspect|air.manage_items|air.manage_files|air.promote_inventory|air.finalize_inspection|air.reopen_inspection|air.archive|air.restore|air.print')
            ->only(['save']);
    }

    public function save(
        SaveAirInspectionItemComponentsRequest $request,
        string $air,
        string $line,
    ): JsonResponse {
        $this->components->save(
            (string) $request->user()?->id,
            $air,
            $line,
            $request->validated('components', []),
        );

        return response()->json([
            'data' => $this->inspectionPayload($air),
            'message' => 'AIR item components saved.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function inspectionPayload(string $air): array
    {
        $payload = $this->inspection->getForInspection($air);
        $airPayload = is_array($payload['air'] ?? null)
            ? $payload['air']
            : [];
        $items = is_array($payload['items'] ?? null)
            ? $payload['items']
            : [];

        $airPayload['items'] = $items;

        return [
            'air' => $airPayload,
            'items' => $items,
        ];
    }
}
