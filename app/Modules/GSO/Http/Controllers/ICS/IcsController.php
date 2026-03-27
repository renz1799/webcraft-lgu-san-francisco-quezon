<?php

namespace App\Modules\GSO\Http\Controllers\ICS;

use App\Core\Models\Department;
use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\ICS\CreateIcsDraftRequest;
use App\Modules\GSO\Http\Requests\ICS\DestroyIcsRequest;
use App\Modules\GSO\Http\Requests\ICS\IcsDataRequest;
use App\Modules\GSO\Http\Requests\ICS\RestoreIcsRequest;
use App\Modules\GSO\Http\Requests\ICS\UpdateIcsRequest;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\Ics;
use App\Modules\GSO\Services\Contracts\ICS\IcsServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IcsController extends Controller
{
    public function __construct(
        private readonly IcsServiceInterface $ics,
    ) {}

    public function index(): View
    {
        return view('gso::ics.index', [
            'departments' => Department::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'fundSources' => FundSource::query()
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
        ]);
    }

    public function edit(Ics $ics): View
    {
        return view('gso::ics.edit', $this->ics->getEditData((string) $ics->id));
    }

    public function data(IcsDataRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $page = max(1, (int) ($validated['page'] ?? 1));
        $size = max(1, min((int) ($validated['size'] ?? 15), 100));

        $filters = $validated;
        unset($filters['page'], $filters['size']);

        $payload = $this->ics->datatable($filters, $page, $size);

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function createDraft(CreateIcsDraftRequest $request): RedirectResponse
    {
        $ics = $this->ics->createDraft((string) $request->user()?->id);

        return redirect()
            ->route('gso.ics.edit', ['ics' => (string) $ics->id])
            ->with('success', 'ICS draft created. Complete the header details before submitting.');
    }

    public function update(UpdateIcsRequest $request, Ics $ics): JsonResponse
    {
        $updated = $this->ics->update(
            actorUserId: (string) $request->user()?->id,
            icsId: (string) $ics->id,
            payload: $request->validated(),
        );

        return response()->json([
            'ok' => true,
            'message' => 'ICS draft updated.',
            'data' => [
                'ics_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function destroy(DestroyIcsRequest $request, Ics $ics): JsonResponse
    {
        $this->ics->delete((string) $request->user()?->id, (string) $ics->id);

        return response()->json([
            'ok' => true,
            'message' => 'ICS archived.',
        ]);
    }

    public function restore(RestoreIcsRequest $request, Ics $ics): JsonResponse
    {
        $this->ics->restore((string) $request->user()?->id, (string) $ics->id);

        return response()->json([
            'ok' => true,
            'message' => 'ICS restored.',
        ]);
    }
}
