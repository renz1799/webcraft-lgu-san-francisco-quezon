<?php

namespace App\Modules\GSO\Http\Controllers\FundSources;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\FundSources\DestroyFundSourceRequest;
use App\Modules\GSO\Http\Requests\FundSources\RestoreFundSourceRequest;
use App\Modules\GSO\Http\Requests\FundSources\StoreFundSourceRequest;
use App\Modules\GSO\Http\Requests\FundSources\UpdateFundSourceRequest;
use App\Modules\GSO\Services\Contracts\FundSourceServiceInterface;
use Illuminate\Http\JsonResponse;

class FundSourceActionController extends Controller
{
    public function __construct(
        private readonly FundSourceServiceInterface $fundSources,
    ) {
        $this->middleware('permission:fund_sources.create|fund_sources.update|fund_sources.archive|fund_sources.restore')
            ->only(['store', 'update', 'destroy', 'restore']);
    }

    public function store(StoreFundSourceRequest $request): JsonResponse
    {
        $fundSource = $this->fundSources->create((string) $request->user()->id, $request->validated());

        return response()->json([
            'message' => 'Fund source created successfully.',
            'data' => $fundSource->only(['id', 'code', 'name', 'fund_cluster_id', 'is_active']),
        ]);
    }

    public function update(UpdateFundSourceRequest $request, string $fundSource): JsonResponse
    {
        $updated = $this->fundSources->update((string) $request->user()->id, $fundSource, $request->validated());

        return response()->json([
            'message' => 'Fund source updated successfully.',
            'data' => $updated->only(['id', 'code', 'name', 'fund_cluster_id', 'is_active']),
        ]);
    }

    public function destroy(DestroyFundSourceRequest $request, string $fundSource): JsonResponse
    {
        $this->fundSources->delete((string) $request->user()->id, $fundSource);

        return response()->json([
            'message' => 'Fund source archived successfully.',
        ]);
    }

    public function restore(RestoreFundSourceRequest $request, string $fundSource): JsonResponse
    {
        $this->fundSources->restore((string) $request->user()->id, $fundSource);

        return response()->json([
            'message' => 'Fund source restored successfully.',
        ]);
    }
}
