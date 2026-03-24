<?php

namespace App\Modules\GSO\Http\Controllers\RIS;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\RIS\DeleteRisRequest;
use App\Modules\GSO\Http\Requests\RIS\RestoreRisRequest;
use App\Modules\GSO\Http\Requests\RIS\RisDataRequest;
use App\Modules\GSO\Http\Requests\RIS\UpdateRisRequest;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Services\Contracts\RIS\RisServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RisController extends Controller
{
    public function __construct(
        private readonly RisServiceInterface $risService,
    ) {
    }

    public function index(): View
    {
        return view('gso::ris.index');
    }

    public function data(RisDataRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $page = max(1, (int) ($validated['page'] ?? 1));
        $size = max(1, min((int) ($validated['size'] ?? 15), 100));

        $filters = $validated;
        unset($filters['page'], $filters['size']);

        $payload = $this->risService->datatable($filters, $page, $size);

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function createDraft(Request $request): RedirectResponse
    {
        $ris = $this->risService->createDraft(
            actorUserId: (string) $request->user()?->id,
        );

        return redirect()->route('gso.ris.edit', ['ris' => (string) $ris->id]);
    }

    public function edit(Ris $ris): View
    {
        return view('gso::ris.edit', $this->risService->getEditData((string) $ris->id));
    }

    public function update(UpdateRisRequest $request, Ris $ris): JsonResponse
    {
        $validated = $request->validated();

        Log::info('[RIS] update() incoming', [
            'ris_id' => (string) $ris->id,
            'user_id' => (string) $request->user()?->id,
            'keys' => array_keys($validated),
        ]);

        $updated = $this->risService->updateRis(
            actorUserId: (string) $request->user()?->id,
            risId: (string) $ris->id,
            data: $validated,
        );

        return response()->json([
            'message' => 'RIS updated successfully.',
            'data' => [
                'ris_id' => (string) $updated->id,
            ],
        ]);
    }

    public function destroy(DeleteRisRequest $request, Ris $ris): JsonResponse
    {
        $this->risService->deleteRis(
            actorUserId: (string) $request->user()?->id,
            risId: (string) $ris->id,
        );

        return response()->json([
            'message' => 'RIS deleted successfully.',
        ]);
    }

    public function restore(RestoreRisRequest $request, Ris $ris): JsonResponse
    {
        $this->risService->restoreRis(
            actorUserId: (string) $request->user()?->id,
            risId: (string) $ris->id,
        );

        return response()->json([
            'message' => 'RIS restored successfully.',
        ]);
    }
}
