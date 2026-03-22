<?php

namespace App\Modules\GSO\Http\Controllers\AssetTypes;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\AssetTypes\AssetTypeTableDataRequest;
use App\Modules\GSO\Services\Contracts\AssetTypeServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AssetTypeController extends Controller
{
    public function __construct(
        private readonly AssetTypeServiceInterface $assetTypes,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view Asset Types')
            ->only(['index', 'data']);
    }

    public function index(): View
    {
        return view('gso::asset-types.index');
    }

    public function data(AssetTypeTableDataRequest $request): JsonResponse
    {
        $payload = $this->assetTypes->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }
}
