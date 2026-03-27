<?php

namespace App\Modules\GSO\Http\Controllers\WMR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\WMR\CreateWmrDraftRequest;
use App\Modules\GSO\Http\Requests\WMR\DestroyWmrRequest;
use App\Modules\GSO\Http\Requests\WMR\RestoreWmrRequest;
use App\Modules\GSO\Http\Requests\WMR\UpdateWmrRequest;
use App\Modules\GSO\Http\Requests\WMR\WmrDataRequest;
use App\Modules\GSO\Models\FundCluster;
use App\Modules\GSO\Models\Wmr;
use App\Modules\GSO\Services\Contracts\WMR\WmrServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class WmrController extends Controller
{
    public function __construct(
        private readonly WmrServiceInterface $wmrs,
    ) {}

    public function index(): View
    {
        return view('gso::wmrs.index', [
            'fundClusters' => FundCluster::query()->orderBy('code')->get(),
        ]);
    }

    public function edit(Wmr $wmr): View
    {
        return view('gso::wmrs.edit', $this->wmrs->getEditData((string) $wmr->id));
    }

    public function data(WmrDataRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $page = max(1, (int) ($validated['page'] ?? 1));
        $size = max(1, min((int) ($validated['size'] ?? 15), 100));

        $filters = $validated;
        unset($filters['page'], $filters['size']);

        $payload = $this->wmrs->datatable($filters, $page, $size);

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function createDraft(CreateWmrDraftRequest $request): JsonResponse
    {
        $wmr = $this->wmrs->createDraft((string) $request->user()->id);

        return response()->json([
            'ok' => true,
            'message' => 'WMR draft created.',
            'data' => [
                'wmr_id' => (string) $wmr->id,
                'status' => (string) $wmr->status,
                'edit_url' => route('gso.wmrs.edit', $wmr->id),
            ],
        ]);
    }

    public function update(UpdateWmrRequest $request, Wmr $wmr): JsonResponse
    {
        $updated = $this->wmrs->update((string) $request->user()->id, (string) $wmr->id, $request->validated());

        return response()->json([
            'ok' => true,
            'message' => 'WMR draft updated.',
            'data' => [
                'wmr_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function destroy(DestroyWmrRequest $request, Wmr $wmr): JsonResponse
    {
        $this->wmrs->delete((string) $request->user()->id, (string) $wmr->id);

        return response()->json([
            'ok' => true,
            'message' => 'WMR archived.',
        ]);
    }

    public function restore(RestoreWmrRequest $request, Wmr $wmr): JsonResponse
    {
        $this->wmrs->restore((string) $request->user()->id, (string) $wmr->id);

        return response()->json([
            'ok' => true,
            'message' => 'WMR restored.',
        ]);
    }
}


