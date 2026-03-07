<?php
// app/Http/Controllers/AuditLogController.php
namespace App\Http\Controllers;

use App\Http\Requests\Logs\AuditLogsDataRequest;
use App\Services\Contracts\AuditLogServiceInterface;
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
        return view('logs.audit-logs');
    }

    public function data(AuditLogsDataRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $page = max(1, (int) ($validated['page'] ?? 1));
        $size = max(1, min((int) ($validated['size'] ?? 15), 100));

        $filters = $validated;
        unset($filters['page'], $filters['size']);

        $payload = $this->audit->datatable($filters, $page, $size);

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }
}


