<?php

namespace App\Modules\GSO\Http\Controllers\InventoryItems;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\InventoryItems\DestroyInventoryItemFileRequest;
use App\Modules\GSO\Http\Requests\InventoryItems\ImportInspectionInventoryFilesRequest;
use App\Modules\GSO\Http\Requests\InventoryItems\StoreInventoryItemFileRequest;
use App\Modules\GSO\Services\Contracts\InventoryItemFileServiceInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class InventoryItemFileController extends Controller
{
    public function __construct(
        private readonly InventoryItemFileServiceInterface $files,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view Inventory Items|modify Inventory Items')
            ->only(['index', 'preview']);

        $this->middleware('role_or_permission:Administrator|admin|modify Inventory Items')
            ->only(['store', 'destroy', 'importInspection']);
    }

    public function index(string $inventoryItem): JsonResponse
    {
        return response()->json([
            'data' => $this->files->listForInventoryItem($inventoryItem),
        ]);
    }

    public function store(StoreInventoryItemFileRequest $request, string $inventoryItem): JsonResponse
    {
        return response()->json([
            'data' => $this->files->upload(
                (string) $request->user()->id,
                $inventoryItem,
                $request->file('files', []),
                $request->validated('type'),
            ),
        ]);
    }

    public function importInspection(ImportInspectionInventoryFilesRequest $request, string $inventoryItem): JsonResponse
    {
        return response()->json([
            'data' => $this->files->importInspectionPhotos(
                (string) $request->user()->id,
                $inventoryItem,
                (string) $request->validated('inspection_id'),
            ),
        ]);
    }

    public function preview(string $inventoryItem, string $file): Response
    {
        $payload = $this->files->preview($inventoryItem, $file);

        return response($payload['bytes'], 200, [
            'Content-Type' => $payload['mime'] ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($payload['name'] ?: 'file') . '"',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function destroy(DestroyInventoryItemFileRequest $request, string $inventoryItem, string $file): JsonResponse
    {
        return response()->json([
            'data' => $this->files->delete((string) $request->user()->id, $inventoryItem, $file),
        ]);
    }
}
