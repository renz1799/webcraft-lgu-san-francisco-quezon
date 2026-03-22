<?php

namespace App\Modules\GSO\Http\Controllers\Items;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Items\ItemTableDataRequest;
use App\Modules\GSO\Models\AssetCategory;
use App\Modules\GSO\Services\Contracts\ItemServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function __construct(
        private readonly ItemServiceInterface $items,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view Items|modify Items')
            ->only(['index', 'data']);
    }

    public function index(): View
    {
        return view('gso::items.index', [
            'assetCategories' => AssetCategory::query()
                ->whereNull('deleted_at')
                ->orderBy('asset_code')
                ->get(['id', 'asset_code', 'asset_name']),
        ]);
    }

    public function data(ItemTableDataRequest $request): JsonResponse
    {
        $payload = $this->items->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }
}
