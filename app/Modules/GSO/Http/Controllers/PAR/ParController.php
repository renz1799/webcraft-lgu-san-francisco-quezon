<?php

namespace App\Modules\GSO\Http\Controllers\PAR;

use App\Core\Models\Department;
use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\PAR\CreateParDraftRequest;
use App\Modules\GSO\Http\Requests\PAR\DestroyParRequest;
use App\Modules\GSO\Http\Requests\PAR\ParDataRequest;
use App\Modules\GSO\Http\Requests\PAR\RestoreParRequest;
use App\Modules\GSO\Http\Requests\PAR\StoreParRequest;
use App\Modules\GSO\Http\Requests\PAR\UpdateParRequest;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Services\Contracts\PAR\ParServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ParController extends Controller
{
    public function __construct(
        private readonly ParServiceInterface $pars,
    ) {
    }

    public function index(): View
    {
        return view('gso::pars.index', [
            'departments' => Department::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
        ]);
    }

    public function data(ParDataRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $page = max(1, (int) ($validated['page'] ?? 1));
        $size = max(1, min((int) ($validated['size'] ?? 15), 100));

        $filters = $validated;
        unset($filters['page'], $filters['size']);

        $payload = $this->pars->datatable($filters, $page, $size);

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()
            ->route('gso.pars.index')
            ->with('info', 'Use Create PAR from the index to open a new draft directly in the working page.');
    }

    public function createDraft(CreateParDraftRequest $request): RedirectResponse
    {
        $par = $this->pars->createDraft((string) $request->user()?->id, []);

        return redirect()
            ->route('gso.pars.show', ['par' => (string) $par->id])
            ->with('success', 'PAR draft created. Complete the header details before submitting.');
    }

    public function store(StoreParRequest $request): RedirectResponse
    {
        $par = $this->pars->createDraft((string) $request->user()?->id, $request->validated());

        return redirect()
            ->route('gso.pars.show', ['par' => (string) $par->id])
            ->with('success', 'PAR draft created.');
    }

    public function show(Par $par): View
    {
        $par->load([
            'department',
            'fundSource.fundCluster',
            'items.inventoryItem.item',
        ]);

        return view('gso::pars.show', [
            'par' => $par,
            'departments' => Department::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'fundSources' => FundSource::query()
                ->with('fundCluster')
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'fund_cluster_id']),
        ]);
    }

    public function update(UpdateParRequest $request, Par $par): JsonResponse
    {
        $updated = $this->pars->update(
            actorUserId: (string) $request->user()?->id,
            parId: (string) $par->id,
            payload: $request->validated(),
        );

        return response()->json([
            'ok' => true,
            'message' => 'PAR draft updated.',
            'data' => [
                'par_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function destroy(DestroyParRequest $request, Par $par): JsonResponse
    {
        $this->pars->delete((string) $request->user()?->id, (string) $par->id);

        return response()->json([
            'ok' => true,
            'message' => 'PAR archived.',
        ]);
    }

    public function restore(RestoreParRequest $request, Par $par): JsonResponse
    {
        $this->pars->restore((string) $request->user()?->id, (string) $par->id);

        return response()->json([
            'ok' => true,
            'message' => 'PAR restored.',
        ]);
    }
}
