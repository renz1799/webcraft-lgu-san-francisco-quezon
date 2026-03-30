<?php

namespace App\Core\Http\Controllers\AccountablePersons;

use App\Core\Http\Requests\AccountablePersons\AccountablePersonTableDataRequest;
use App\Core\Models\Department;
use App\Core\Services\Contracts\AccountablePersons\AccountablePersonServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountablePersonController extends Controller
{
    public function __construct(
        private readonly AccountablePersonServiceInterface $accountablePersons,
    ) {
        $this->middleware('permission:accountable_persons.view')
            ->only(['index', 'data', 'suggest']);
    }

    public function index(): View
    {
        return view('reference-data.accountable-persons.index', [
            'departments' => Department::query()
                ->whereNull('deleted_at')
                ->orderBy('code')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
        ]);
    }

    public function data(AccountablePersonTableDataRequest $request): JsonResponse
    {
        $payload = $this->accountablePersons->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        return response()->json([
            'items' => $this->accountablePersons->suggest((string) $request->query('q', '')),
        ]);
    }
}
