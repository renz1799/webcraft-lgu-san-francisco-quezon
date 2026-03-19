<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logs\LoginLogsDataRequest;
use App\Services\Contracts\Access\LoginLogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LoginLogController extends Controller
{
    public function __construct(
        private readonly LoginLogServiceInterface $logs
    ) {
        $this->middleware(['auth', 'role_or_permission:Administrator']);
    }

    public function index(): View
    {
        return view('logs.logins.index');
    }

    public function data(LoginLogsDataRequest $request): JsonResponse
    {
        $payload = $this->logs->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int)($payload['last_page'] ?? 1),
            'total' => (int)($payload['total'] ?? 0),
        ]);
    }
}


