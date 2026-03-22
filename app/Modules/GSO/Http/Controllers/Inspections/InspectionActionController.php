<?php

namespace App\Modules\GSO\Http\Controllers\Inspections;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Inspections\DestroyInspectionRequest;
use App\Modules\GSO\Http\Requests\Inspections\RestoreInspectionRequest;
use App\Modules\GSO\Http\Requests\Inspections\StoreInspectionRequest;
use App\Modules\GSO\Http\Requests\Inspections\UpdateInspectionRequest;
use App\Modules\GSO\Services\Contracts\InspectionServiceInterface;
use Illuminate\Http\JsonResponse;

class InspectionActionController extends Controller
{
    public function __construct(
        private readonly InspectionServiceInterface $inspections,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|modify Inspections')
            ->only(['show', 'store', 'update', 'destroy', 'restore']);
    }

    public function show(string $inspection): JsonResponse
    {
        return response()->json([
            'data' => $this->inspections->getForEdit($inspection),
        ]);
    }

    public function store(StoreInspectionRequest $request): JsonResponse
    {
        $inspection = $this->inspections->create((string) $request->user()->id, $request->validated());

        return response()->json([
            'data' => $this->inspections->getForEdit((string) $inspection->id),
        ]);
    }

    public function update(UpdateInspectionRequest $request, string $inspection): JsonResponse
    {
        $updated = $this->inspections->update((string) $request->user()->id, $inspection, $request->validated());

        return response()->json([
            'data' => $this->inspections->getForEdit((string) $updated->id),
        ]);
    }

    public function destroy(DestroyInspectionRequest $request, string $inspection): JsonResponse
    {
        $this->inspections->delete((string) $request->user()->id, $inspection);

        return response()->json(['ok' => true]);
    }

    public function restore(RestoreInspectionRequest $request, string $inspection): JsonResponse
    {
        $this->inspections->restore((string) $request->user()->id, $inspection);

        return response()->json(['ok' => true]);
    }
}
