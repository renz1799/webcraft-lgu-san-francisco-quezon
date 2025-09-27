<?php
// app/Http/Controllers/AuditLogController.php
namespace App\Http\Controllers;

use App\Http\Requests\Logs\LogIndexRequest;
use App\Services\Contracts\AuditLogServiceInterface;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditLogServiceInterface $audit
    ) {
        $this->middleware(['auth','role_or_permission:admin|view Audit Logs']);
    }

    public function index(LogIndexRequest $request): View
    {
        $filters = $request->validated();
        $perPage = (int)($filters['per_page'] ?? 50);
        $logs    = $this->audit->paginate($perPage, $filters);

        // ⬇️ updated view name
        return view('logs.audit-logs', compact('logs', 'filters'));
    }
}
