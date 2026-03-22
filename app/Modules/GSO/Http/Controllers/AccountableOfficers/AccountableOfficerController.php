<?php

namespace App\Modules\GSO\Http\Controllers\AccountableOfficers;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\AccountableOfficers\AccountableOfficerTableDataRequest;
use App\Modules\GSO\Services\Contracts\AccountableOfficerServiceInterface;
use App\Modules\GSO\Services\Contracts\DepartmentServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountableOfficerController extends Controller
{
    public function __construct(
        private readonly AccountableOfficerServiceInterface $accountableOfficers,
        private readonly DepartmentServiceInterface $departments,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view Accountable Officers')
            ->only(['index', 'data', 'suggest']);
    }

    public function index(): View
    {
        return view('gso::accountable-officers.index', [
            'departments' => $this->departments->optionsForSelect(),
        ]);
    }

    public function data(AccountableOfficerTableDataRequest $request): JsonResponse
    {
        $payload = $this->accountableOfficers->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        return response()->json([
            'items' => $this->accountableOfficers->suggest((string) $request->query('q', '')),
        ]);
    }
}
