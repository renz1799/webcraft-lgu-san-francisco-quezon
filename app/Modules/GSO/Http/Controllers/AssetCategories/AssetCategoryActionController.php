<?php

namespace App\Modules\GSO\Http\Controllers\AssetCategories;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\AssetCategories\DestroyAssetCategoryRequest;
use App\Modules\GSO\Http\Requests\AssetCategories\RestoreAssetCategoryRequest;
use App\Modules\GSO\Http\Requests\AssetCategories\StoreAssetCategoryRequest;
use App\Modules\GSO\Http\Requests\AssetCategories\UpdateAssetCategoryRequest;
use App\Modules\GSO\Services\Contracts\AssetCategoryServiceInterface;
use Illuminate\Http\JsonResponse;

class AssetCategoryActionController extends Controller
{
    public function __construct(
        private readonly AssetCategoryServiceInterface $assetCategories,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|modify Asset Categories')
            ->only(['store', 'update', 'destroy', 'restore']);
    }

    public function store(StoreAssetCategoryRequest $request): JsonResponse
    {
        $assetCategory = $this->assetCategories->create((string) $request->user()->id, $request->validated());

        return response()->json([
            'message' => 'Asset category created successfully.',
            'data' => $assetCategory->only(['id', 'asset_type_id', 'asset_code', 'asset_name', 'account_group']),
        ]);
    }

    public function update(UpdateAssetCategoryRequest $request, string $assetCategory): JsonResponse
    {
        $updated = $this->assetCategories->update((string) $request->user()->id, $assetCategory, $request->validated());

        return response()->json([
            'message' => 'Asset category updated successfully.',
            'data' => $updated->only(['id', 'asset_type_id', 'asset_code', 'asset_name', 'account_group']),
        ]);
    }

    public function destroy(DestroyAssetCategoryRequest $request, string $assetCategory): JsonResponse
    {
        $this->assetCategories->delete((string) $request->user()->id, $assetCategory);

        return response()->json([
            'message' => 'Asset category archived successfully.',
        ]);
    }

    public function restore(RestoreAssetCategoryRequest $request, string $assetCategory): JsonResponse
    {
        $this->assetCategories->restore((string) $request->user()->id, $assetCategory);

        return response()->json([
            'message' => 'Asset category restored successfully.',
        ]);
    }
}
