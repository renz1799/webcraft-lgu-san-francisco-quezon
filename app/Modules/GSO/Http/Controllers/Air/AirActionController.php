<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\CreateAirFollowUpRequest;
use App\Modules\GSO\Http\Requests\Air\DestroyAirRequest;
use App\Modules\GSO\Http\Requests\Air\ForceDestroyAirRequest;
use App\Modules\GSO\Http\Requests\Air\RestoreAirRequest;
use App\Modules\GSO\Http\Requests\Air\SubmitAirDraftRequest;
use App\Modules\GSO\Http\Requests\Air\UpdateAirDraftRequest;
use App\Modules\GSO\Services\Contracts\Air\AirServiceInterface;
use Illuminate\Http\JsonResponse;

class AirActionController extends Controller
{
    public function __construct(
        private readonly AirServiceInterface $airs,
    ) {
        $this->middleware('permission:air.create|air.update|air.manage_items|air.manage_files|air.promote_inventory|air.reopen_inspection|air.archive|air.restore|air.print')
            ->only(['update', 'submit', 'createFollowUp', 'destroy', 'restore', 'forceDestroy']);
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

    public function createFollowUp(CreateAirFollowUpRequest $request, string $air): JsonResponse
    {
        $followUp = $this->airs->createFollowUpFromInspection((string) $request->user()?->id, $air);
        $status = (string) ($followUp->status ?? '');
        $redirectUrl = in_array($status, ['submitted', 'in_progress', 'inspected'], true)
            ? route('gso.air.inspect', ['air' => $followUp->id])
            : route('gso.air.edit', ['air' => $followUp->id]);

        return response()->json([
            'data' => [
                'id' => (string) $followUp->id,
                'status' => $status,
                'redirect_url' => $redirectUrl,
            ],
            'message' => 'Follow-up AIR submitted and ready for inspection.',
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
