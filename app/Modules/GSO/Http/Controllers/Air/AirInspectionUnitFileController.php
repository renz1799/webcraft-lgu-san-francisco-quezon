<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\DestroyAirInspectionUnitFileRequest;
use App\Modules\GSO\Http\Requests\Air\SetPrimaryAirInspectionUnitFileRequest;
use App\Modules\GSO\Http\Requests\Air\StoreAirInspectionUnitFileRequest;
use App\Modules\GSO\Services\Contracts\AirInspectionUnitFileServiceInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AirInspectionUnitFileController extends Controller
{
    public function __construct(
        private readonly AirInspectionUnitFileServiceInterface $files,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view AIR|modify AIR')
            ->only(['index', 'preview']);

        $this->middleware('role_or_permission:Administrator|admin|modify AIR')
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
        return response()->json([
            'data' => $this->files->upload(
                (string) $request->user()?->id,
                $air,
                $airItem,
                $unit,
                $request->file('files', []),
                $request->validated('type'),
            ),
            'message' => 'Unit files uploaded.',
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
            'message' => 'Unit file deleted.',
        ]);
    }

    public function setPrimary(SetPrimaryAirInspectionUnitFileRequest $request, string $air, string $airItem, string $unit, string $file): JsonResponse
    {
        return response()->json([
            'data' => $this->files->setPrimary((string) $request->user()?->id, $air, $airItem, $unit, $file),
            'message' => 'Primary unit file updated.',
        ]);
    }
}
