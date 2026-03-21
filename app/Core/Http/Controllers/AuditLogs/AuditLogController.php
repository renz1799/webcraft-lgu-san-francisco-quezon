<?php

namespace App\Core\Http\Controllers\AuditLogs;

use App\Http\Controllers\Controller;
use App\Core\Http\Requests\AuditLogs\AuditLogsDataRequest;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditLogServiceInterface $audit
    ) {
        $this->middleware(['auth', 'role_or_permission:Administrator|admin|view Audit Logs']);
    }

    public function index(): View
    {
        return view('logs.audits.index');
    }

    public function data(AuditLogsDataRequest $request): JsonResponse
    {
        $payload = $this->audit->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }
}
