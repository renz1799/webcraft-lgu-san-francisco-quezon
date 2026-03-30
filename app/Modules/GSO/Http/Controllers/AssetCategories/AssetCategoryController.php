<?php

namespace App\Modules\GSO\Http\Controllers\AssetCategories;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\AssetCategories\AssetCategoryTableDataRequest;
use App\Modules\GSO\Services\Contracts\AssetCategoryServiceInterface;
use App\Modules\GSO\Services\Contracts\AssetTypeServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AssetCategoryController extends Controller
{
    public function __construct(
        private readonly AssetCategoryServiceInterface $assetCategories,
        private readonly AssetTypeServiceInterface $assetTypes,
    ) {
        $this->middleware('permission:asset_categories.view|asset_categories.create|asset_categories.update|asset_categories.archive|asset_categories.restore')
            ->only(['index', 'data']);
    }

    public function index(): View
    {
        return view('gso::asset-categories.index', [
            'assetTypes' => $this->assetTypes->optionsForSelect(),
        ]);
    }

    public function data(AssetCategoryTableDataRequest $request): JsonResponse
    {
        $payload = $this->assetCategories->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }
}
