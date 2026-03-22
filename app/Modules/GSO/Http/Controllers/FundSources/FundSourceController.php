<?php

namespace App\Modules\GSO\Http\Controllers\FundSources;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\FundSources\FundSourceTableDataRequest;
use App\Modules\GSO\Services\Contracts\FundClusterServiceInterface;
use App\Modules\GSO\Services\Contracts\FundSourceServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class FundSourceController extends Controller
{
    public function __construct(
        private readonly FundSourceServiceInterface $fundSources,
        private readonly FundClusterServiceInterface $fundClusters,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view Fund Sources')
            ->only(['index', 'data']);
    }

    public function index(): View
    {
        return view('gso::fund-sources.index', [
            'fundClusters' => $this->fundClusters->optionsForSelect(),
        ]);
    }

    public function data(FundSourceTableDataRequest $request): JsonResponse
    {
        $payload = $this->fundSources->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }
}
