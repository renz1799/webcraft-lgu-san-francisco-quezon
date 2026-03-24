<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\DestroyAirFileRequest;
use App\Modules\GSO\Http\Requests\Air\SetPrimaryAirFileRequest;
use App\Modules\GSO\Http\Requests\Air\StoreAirFileRequest;
use App\Modules\GSO\Services\Contracts\Air\AirFileServiceInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AirFileController extends Controller
{
    public function __construct(
        private readonly AirFileServiceInterface $files,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view AIR|modify AIR')
            ->only(['index', 'preview']);

        $this->middleware('role_or_permission:Administrator|admin|modify AIR')
            ->only(['store', 'destroy', 'setPrimary']);
    }

    public function index(string $air): JsonResponse
    {
        return response()->json([
            'data' => $this->files->listForAir($air),
        ]);
    }

    public function store(StoreAirFileRequest $request, string $air): JsonResponse
    {
        return response()->json([
            'data' => $this->files->upload(
                (string) $request->user()?->id,
                $air,
                $request->file('files', []),
                $request->validated('type'),
            ),
            'message' => 'AIR files uploaded.',
        ]);
    }

    public function preview(string $air, string $file): Response
    {
        $payload = $this->files->preview($air, $file);

        return response($payload['bytes'], 200, [
            'Content-Type' => $payload['mime'] ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($payload['name'] ?: 'file') . '"',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function destroy(DestroyAirFileRequest $request, string $air, string $file): JsonResponse
    {
        return response()->json([
            'data' => $this->files->delete((string) $request->user()?->id, $air, $file),
            'message' => 'AIR file deleted.',
        ]);
    }

    public function setPrimary(SetPrimaryAirFileRequest $request, string $air, string $file): JsonResponse
    {
        return response()->json([
            'data' => $this->files->setPrimary((string) $request->user()?->id, $air, $file),
            'message' => 'Primary AIR file updated.',
        ]);
    }
}
