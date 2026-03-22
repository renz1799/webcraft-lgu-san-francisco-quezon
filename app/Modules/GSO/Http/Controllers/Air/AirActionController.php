<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\DestroyAirRequest;
use App\Modules\GSO\Http\Requests\Air\ForceDestroyAirRequest;
use App\Modules\GSO\Http\Requests\Air\RestoreAirRequest;
use App\Modules\GSO\Http\Requests\Air\SubmitAirDraftRequest;
use App\Modules\GSO\Http\Requests\Air\UpdateAirDraftRequest;
use App\Modules\GSO\Services\Contracts\AirServiceInterface;
use Illuminate\Http\JsonResponse;

class AirActionController extends Controller
{
    public function __construct(
        private readonly AirServiceInterface $airs,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|modify AIR')
            ->only(['update', 'submit', 'destroy', 'restore', 'forceDestroy']);
    }

    public function update(UpdateAirDraftRequest $request, string $air): JsonResponse
    {
        $updated = $this->airs->updateDraft((string) $request->user()?->id, $air, $request->validated());

        return response()->json([
            'data' => $this->airs->getForEdit((string) $updated->id),
            'message' => 'AIR draft saved.',
        ]);
    }

    public function submit(SubmitAirDraftRequest $request, string $air): JsonResponse
    {
        $submitted = $this->airs->submitDraft((string) $request->user()?->id, $air);

        return response()->json([
            'data' => $this->airs->getForEdit((string) $submitted->id),
            'message' => 'AIR submitted.',
        ]);
    }

    public function destroy(DestroyAirRequest $request, string $air): JsonResponse
    {
        $this->airs->delete((string) $request->user()?->id, $air);

        return response()->json(['ok' => true]);
    }

    public function restore(RestoreAirRequest $request, string $air): JsonResponse
    {
        $this->airs->restore((string) $request->user()?->id, $air);

        return response()->json(['ok' => true]);
    }

    public function forceDestroy(ForceDestroyAirRequest $request, string $air): JsonResponse
    {
        $this->airs->forceDelete((string) $request->user()?->id, $air);

        return response()->json(['ok' => true]);
    }
}
