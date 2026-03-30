<?php

namespace App\Modules\GSO\Http\Controllers\AssetTypes;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\AssetTypes\DestroyAssetTypeRequest;
use App\Modules\GSO\Http\Requests\AssetTypes\RestoreAssetTypeRequest;
use App\Modules\GSO\Http\Requests\AssetTypes\StoreAssetTypeRequest;
use App\Modules\GSO\Http\Requests\AssetTypes\UpdateAssetTypeRequest;
use App\Modules\GSO\Services\Contracts\AssetTypeServiceInterface;
use Illuminate\Http\JsonResponse;

class AssetTypeActionController extends Controller
{
    public function __construct(
        private readonly AssetTypeServiceInterface $assetTypes,
    ) {
        $this->middleware('permission:asset_types.create|asset_types.update|asset_types.archive|asset_types.restore')
            ->only(['store', 'update', 'destroy', 'restore']);
    }

    public function store(StoreAssetTypeRequest $request): JsonResponse
    {
        $assetType = $this->assetTypes->create((string) $request->user()->id, $request->validated());

        return response()->json([
            'message' => 'Asset type created successfully.',
            'data' => $assetType->only(['id', 'type_code', 'type_name']),
        ]);
    }

    public function update(UpdateAssetTypeRequest $request, string $assetType): JsonResponse
    {
        $updated = $this->assetTypes->update((string) $request->user()->id, $assetType, $request->validated());

        return response()->json([
            'message' => 'Asset type updated successfully.',
            'data' => $updated->only(['id', 'type_code', 'type_name']),
        ]);
    }

    public function destroy(DestroyAssetTypeRequest $request, string $assetType): JsonResponse
    {
        $this->assetTypes->delete((string) $request->user()->id, $assetType);

        return response()->json([
            'message' => 'Asset type archived successfully.',
        ]);
    }

    public function restore(RestoreAssetTypeRequest $request, string $assetType): JsonResponse
    {
        $this->assetTypes->restore((string) $request->user()->id, $assetType);

        return response()->json([
            'message' => 'Asset type restored successfully.',
        ]);
    }
}
