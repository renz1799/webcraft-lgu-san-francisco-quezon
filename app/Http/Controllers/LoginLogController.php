<?php

namespace App\Http\Controllers;

use App\Http\Requests\Logs\LoginLogsDataRequest;
use App\Services\Contracts\LoginLogServiceInterface;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class LoginLogController extends Controller
{
    public function __construct(
        private readonly LoginLogServiceInterface $logs
    ) {
        $this->middleware(['auth', 'role_or_permission:admin|view Login Logs']);
    }

    /** Page */
    public function index(): View
    {
        return view('logs.index');
    }

    /** DataTables JSON */
    public function data(LoginLogsDataRequest $request): JsonResponse
    {
        $payload = $this->logs->datatable($request->validated());

        return response()->json([
            'draw'            => (int) ($request->input('draw') ?? 0),
            'recordsTotal'    => $payload['recordsTotal'],
            'recordsFiltered' => $payload['recordsFiltered'],
            'data'            => $payload['data'],
        ]);
    }
}
