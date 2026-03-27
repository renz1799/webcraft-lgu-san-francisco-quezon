<?php

namespace App\Modules\GSO\Http\Controllers\ITR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\ITR\CreateItrDraftRequest;
use App\Modules\GSO\Http\Requests\ITR\DestroyItrRequest;
use App\Modules\GSO\Http\Requests\ITR\ItrDataRequest;
use App\Modules\GSO\Http\Requests\ITR\RestoreItrRequest;
use App\Modules\GSO\Http\Requests\ITR\UpdateItrRequest;
use App\Core\Models\Department;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\Itr;
use App\Modules\GSO\Services\Contracts\ITR\ItrServiceInterface;
use Illuminate\Http\JsonResponse;

class ItrController extends Controller
{
    public function __construct(
        private readonly ItrServiceInterface $itrs,
    ) {}

    public function index()
    {
        return view('gso::itrs.index', [
            'departments' => Department::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'fundSources' => FundSource::query()
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'fund_cluster_id']),
        ]);
    }

    public function edit(Itr $itr)
    {
        return view('gso::itrs.edit', $this->itrs->getEditData((string) $itr->id));
    }

    public function data(ItrDataRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $page = max(1, (int) ($validated['page'] ?? 1));
        $size = max(1, min((int) ($validated['size'] ?? 15), 100));

        $filters = $validated;
        unset($filters['page'], $filters['size']);

        $payload = $this->itrs->datatable($filters, $page, $size);

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function createDraft(CreateItrDraftRequest $request): JsonResponse
    {
        $itr = $this->itrs->createDraft((string) $request->user()->id);

        return response()->json([
            'ok' => true,
            'message' => 'ITR draft created.',
            'data' => [
                'itr_id' => (string) $itr->id,
                'status' => (string) $itr->status,
                'edit_url' => route('gso.itrs.edit', $itr->id),
            ],
        ]);
    }

    public function update(UpdateItrRequest $request, Itr $itr): JsonResponse
    {
        $updated = $this->itrs->update((string) $request->user()->id, (string) $itr->id, $request->validated());

        return response()->json([
            'ok' => true,
            'message' => 'ITR draft updated.',
            'data' => [
                'itr_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function destroy(DestroyItrRequest $request, Itr $itr): JsonResponse
    {
        $this->itrs->delete((string) $request->user()->id, (string) $itr->id);

        return response()->json([
            'ok' => true,
            'message' => 'ITR archived.',
        ]);
    }

    public function restore(RestoreItrRequest $request, Itr $itr): JsonResponse
    {
        $this->itrs->restore((string) $request->user()->id, (string) $itr->id);

        return response()->json([
            'ok' => true,
            'message' => 'ITR restored.',
        ]);
    }
}



