<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Core\Models\Department;
use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\AirTableDataRequest;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Services\Contracts\AirServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AirController extends Controller
{
    public function __construct(
        private readonly AirServiceInterface $airs,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view AIR|modify AIR')
            ->only(['index', 'data', 'edit']);

        $this->middleware('role_or_permission:Administrator|admin|modify AIR')
            ->only(['create']);
    }

    public function index(): View
    {
        return view('gso::air.index');
    }

    public function data(AirTableDataRequest $request): JsonResponse
    {
        $payload = $this->airs->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function create(): RedirectResponse
    {
        $air = $this->airs->createBlankDraft((string) request()->user()?->id);

        return redirect()->route('gso.air.edit', ['air' => (string) $air->id]);
    }

    public function edit(string $air): View
    {
        $airRecord = $this->airs->getForEdit($air);

        return view('gso::air.edit', [
            'air' => $airRecord,
            'departments' => Department::query()
                ->withTrashed()
                ->orderBy('code')
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'deleted_at']),
            'fundSources' => FundSource::query()
                ->withTrashed()
                ->orderBy('code')
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'deleted_at']),
        ]);
    }
}
