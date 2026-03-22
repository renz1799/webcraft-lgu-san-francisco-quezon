<?php

namespace App\Modules\GSO\Http\Controllers\FundClusters;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\FundClusters\DestroyFundClusterRequest;
use App\Modules\GSO\Http\Requests\FundClusters\RestoreFundClusterRequest;
use App\Modules\GSO\Http\Requests\FundClusters\StoreFundClusterRequest;
use App\Modules\GSO\Http\Requests\FundClusters\UpdateFundClusterRequest;
use App\Modules\GSO\Services\Contracts\FundClusterServiceInterface;
use Illuminate\Http\JsonResponse;

class FundClusterActionController extends Controller
{
    public function __construct(
        private readonly FundClusterServiceInterface $fundClusters,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|modify Fund Clusters')
            ->only(['store', 'update', 'destroy', 'restore']);
    }

    public function store(StoreFundClusterRequest $request): JsonResponse
    {
        $fundCluster = $this->fundClusters->create((string) $request->user()->id, $request->validated());

        return response()->json([
            'message' => 'Fund cluster created successfully.',
            'data' => $fundCluster->only(['id', 'code', 'name', 'is_active']),
        ]);
    }

    public function update(UpdateFundClusterRequest $request, string $fundCluster): JsonResponse
    {
        $updated = $this->fundClusters->update((string) $request->user()->id, $fundCluster, $request->validated());

        return response()->json([
            'message' => 'Fund cluster updated successfully.',
            'data' => $updated->only(['id', 'code', 'name', 'is_active']),
        ]);
    }

    public function destroy(DestroyFundClusterRequest $request, string $fundCluster): JsonResponse
    {
        $this->fundClusters->delete((string) $request->user()->id, $fundCluster);

        return response()->json([
            'message' => 'Fund cluster archived successfully.',
        ]);
    }

    public function restore(RestoreFundClusterRequest $request, string $fundCluster): JsonResponse
    {
        $this->fundClusters->restore((string) $request->user()->id, $fundCluster);

        return response()->json([
            'message' => 'Fund cluster restored successfully.',
        ]);
    }
}
