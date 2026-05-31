<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\DestroyAirInspectionItemImageRequest;
use App\Modules\GSO\Http\Requests\Air\StoreAirInspectionItemImageRequest;
use App\Modules\GSO\Services\Air\AirInspectionItemImageService;
use App\Modules\GSO\Services\Contracts\Air\AirInspectionServiceInterface;
use Illuminate\Http\JsonResponse;

class AirInspectionItemImageController extends Controller
{
    public function __construct(
        private readonly AirInspectionItemImageService $images,
        private readonly AirInspectionServiceInterface $inspection,
    ) {
        $this->middleware('permission:air.create|air.update|air.inspect|air.manage_items|air.manage_files|air.promote_inventory|air.finalize_inspection|air.reopen_inspection|air.archive|air.restore|air.print')
            ->only(['store', 'destroy']);
    }

    public function store(
        StoreAirInspectionItemImageRequest $request,
        string $air,
        string $line,
    ): JsonResponse {
        $uploads = array_merge(
            $request->file('images', []),
            $request->file('photos', []),
            $request->file('files', []),
        );

        $this->images->upload(
            (string) $request->user()?->id,
            $air,
            $line,
            $uploads,
        );

        return response()->json([
            'data' => $this->inspectionPayload($air),
            'message' => 'AIR item images uploaded.',
        ]);
    }

    public function destroy(
        DestroyAirInspectionItemImageRequest $request,
        string $air,
        string $line,
        string $image,
    ): JsonResponse {
        $this->images->delete(
            (string) $request->user()?->id,
            $air,
            $line,
            $image,
        );

        return response()->json([
            'data' => $this->inspectionPayload($air),
            'message' => 'AIR item image deleted.',
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
