<?php

namespace App\Modules\GSO\Http\Controllers\Inspections;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Inspections\DestroyInspectionPhotoRequest;
use App\Modules\GSO\Http\Requests\Inspections\StoreInspectionPhotoRequest;
use App\Modules\GSO\Services\Contracts\InspectionPhotoServiceInterface;
use Illuminate\Http\JsonResponse;

class InspectionPhotoController extends Controller
{
    public function __construct(
        private readonly InspectionPhotoServiceInterface $photos,
    ) {
        $this->middleware('permission:inspections.view|inspections.create|inspections.update|inspections.archive|inspections.restore|inspections.manage_photos')
            ->only(['index']);

        $this->middleware('permission:inspections.create|inspections.update|inspections.archive|inspections.restore|inspections.manage_photos')
            ->only(['store', 'destroy']);
    }

    public function index(string $inspection): JsonResponse
    {
        return response()->json([
            'data' => $this->photos->listForInspection($inspection),
        ]);
    }

    public function store(StoreInspectionPhotoRequest $request, string $inspection): JsonResponse
    {
        return response()->json([
            'data' => $this->photos->upload(
                (string) $request->user()->id,
                $inspection,
                $request->file('photos', []),
            ),
        ]);
    }

    public function destroy(DestroyInspectionPhotoRequest $request, string $inspection, string $photo): JsonResponse
    {
        return response()->json([
            'data' => $this->photos->delete((string) $request->user()->id, $inspection, $photo),
        ]);
    }
}
