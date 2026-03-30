<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\DestroyAirInspectionUnitFileRequest;
use App\Modules\GSO\Http\Requests\Air\SetPrimaryAirInspectionUnitFileRequest;
use App\Modules\GSO\Http\Requests\Air\StoreAirInspectionUnitFileRequest;
use App\Modules\GSO\Services\Contracts\Air\AirInspectionUnitFileServiceInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AirInspectionUnitFileController extends Controller
{
    public function __construct(
        private readonly AirInspectionUnitFileServiceInterface $files,
    ) {
        $this->middleware('permission:air.view|air.create|air.update|air.inspect|air.manage_items|air.manage_files|air.promote_inventory|air.finalize_inspection|air.reopen_inspection|air.archive|air.restore|air.print')
            ->only(['index', 'preview']);

        $this->middleware('permission:air.create|air.update|air.inspect|air.manage_items|air.manage_files|air.promote_inventory|air.finalize_inspection|air.reopen_inspection|air.archive|air.restore|air.print')
            ->only(['store', 'destroy', 'setPrimary']);
    }

    public function index(string $air, string $airItem, string $unit): JsonResponse
    {
        return response()->json([
            'data' => $this->files->listForUnit($air, $airItem, $unit),
        ]);
    }

    public function store(StoreAirInspectionUnitFileRequest $request, string $air, string $airItem, string $unit): JsonResponse
    {
        $uploads = array_merge(
            $request->file('photos', []),
            $request->file('files', []),
        );

        return response()->json([
            'data' => $this->files->upload(
                (string) $request->user()?->id,
                $air,
                $airItem,
                $unit,
                $uploads,
                $request->validated('type'),
                $request->validated('caption'),
            ),
            'message' => 'Unit images uploaded.',
        ]);
    }

    public function preview(string $air, string $airItem, string $unit, string $file): Response
    {
        $payload = $this->files->preview($air, $airItem, $unit, $file);

        return response($payload['bytes'], 200, [
            'Content-Type' => $payload['mime'] ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($payload['name'] ?: 'file') . '"',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function destroy(DestroyAirInspectionUnitFileRequest $request, string $air, string $airItem, string $unit, string $file): JsonResponse
    {
        return response()->json([
            'data' => $this->files->delete((string) $request->user()?->id, $air, $airItem, $unit, $file),
            'message' => 'Unit image deleted.',
        ]);
    }

    public function setPrimary(SetPrimaryAirInspectionUnitFileRequest $request, string $air, string $airItem, string $unit, string $file): JsonResponse
    {
        return response()->json([
            'data' => $this->files->setPrimary((string) $request->user()?->id, $air, $airItem, $unit, $file),
            'message' => 'Primary unit image updated.',
        ]);
    }
}
