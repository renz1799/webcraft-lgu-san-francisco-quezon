<?php

namespace App\Modules\GSO\Http\Controllers\PTR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\PTR\CreatePtrDraftRequest;
use App\Modules\GSO\Http\Requests\PTR\DestroyPtrRequest;
use App\Modules\GSO\Http\Requests\PTR\PtrDataRequest;
use App\Modules\GSO\Http\Requests\PTR\RestorePtrRequest;
use App\Modules\GSO\Http\Requests\PTR\UpdatePtrRequest;
use App\Core\Models\Department;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\Ptr;
use App\Modules\GSO\Services\Contracts\PTR\PtrServiceInterface;
use Illuminate\Http\JsonResponse;

class PtrController extends Controller
{
    public function __construct(
        private readonly PtrServiceInterface $ptrs,
    ) {}

    public function index(): \Illuminate\View\View
    {
        return view('gso::ptrs.index', [
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

    public function edit(Ptr $ptr): \Illuminate\View\View
    {
        return view('gso::ptrs.edit', $this->ptrs->getEditData((string) $ptr->id));
    }

    public function data(PtrDataRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $page = max(1, (int) ($validated['page'] ?? 1));
        $size = max(1, min((int) ($validated['size'] ?? 15), 100));

        $filters = $validated;
        unset($filters['page'], $filters['size']);

        $payload = $this->ptrs->datatable($filters, $page, $size);

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function createDraft(CreatePtrDraftRequest $request): JsonResponse
    {
        $ptr = $this->ptrs->createDraft((string) $request->user()->id);

        return response()->json([
            'ok' => true,
            'message' => 'PTR draft created.',
            'data' => [
                'ptr_id' => (string) $ptr->id,
                'status' => (string) $ptr->status,
                'edit_url' => route('gso.ptrs.edit', $ptr->id),
            ],
        ]);
    }

    public function update(UpdatePtrRequest $request, Ptr $ptr): JsonResponse
    {
        $updated = $this->ptrs->update((string) $request->user()->id, (string) $ptr->id, $request->validated());

        return response()->json([
            'ok' => true,
            'message' => 'PTR draft updated.',
            'data' => [
                'ptr_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function destroy(DestroyPtrRequest $request, Ptr $ptr): JsonResponse
    {
        $this->ptrs->delete((string) $request->user()->id, (string) $ptr->id);

        return response()->json([
            'ok' => true,
            'message' => 'PTR archived.',
        ]);
    }

    public function restore(RestorePtrRequest $request, Ptr $ptr): JsonResponse
    {
        $this->ptrs->restore((string) $request->user()->id, (string) $ptr->id);

        return response()->json([
            'ok' => true,
            'message' => 'PTR restored.',
        ]);
    }
}
