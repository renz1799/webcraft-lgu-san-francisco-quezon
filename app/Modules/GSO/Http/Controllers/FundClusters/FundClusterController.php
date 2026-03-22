<?php

namespace App\Modules\GSO\Http\Controllers\FundClusters;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\FundClusters\FundClusterTableDataRequest;
use App\Modules\GSO\Services\Contracts\FundClusterServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class FundClusterController extends Controller
{
    public function __construct(
        private readonly FundClusterServiceInterface $fundClusters,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view Fund Clusters')
            ->only(['index', 'data']);
    }

    public function index(): View
    {
        return view('gso::fund-clusters.index');
    }

    public function data(FundClusterTableDataRequest $request): JsonResponse
    {
        $payload = $this->fundClusters->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }
}
