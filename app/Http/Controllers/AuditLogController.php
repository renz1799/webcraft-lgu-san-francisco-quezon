<?php
// app/Http/Controllers/AuditLogController.php
namespace App\Http\Controllers;

use App\Http\Requests\Logs\LogIndexRequest;
use App\Services\Contracts\AuditLogServiceInterface;
use App\Services\Contracts\AuditLogTableServiceInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditLogServiceInterface $audit,
        private readonly AuditLogTableServiceInterface $auditTable
    ) {
        $this->middleware(['auth','role_or_permission:admin|view Audit Logs']);
    }

    public function index(LogIndexRequest $request): \Illuminate\View\View
    {
        $filters = $request->filters();
        $perPage = $filters['per_page'];
        unset($filters['per_page']);

        $logs = $this->audit->paginate($perPage, $filters);

        return view('logs.audit-logs', compact('logs', 'filters'));
    }

    public function restore(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string',
            'id'   => 'required|string',
        ]);

        // Whitelist restorable types
        $map = [
            User::class       => User::class,
            Permission::class => Permission::class,
            // add Role::class etc if needed
        ];

        abort_unless(isset($map[$data['type']]), 422, 'Unsupported subject type.');

        $class = $map[$data['type']];
        $model = $class::withTrashed()->findOrFail($data['id']);

        // optional: policies/authorization
        if (Gate::denies('restore', $model)) {
            abort(403, 'You are not allowed to restore this resource.');
        }

        if (!method_exists($model, 'restore')) {
            abort(422, 'Model cannot be restored.');
        }

        $model->restore();

        // (optional) record an audit entry with your Audit service
        // app(AuditLogServiceInterface::class)->record(
        //   strtolower(class_basename($class)).'.restored', $model, [], ['restored' => true], [
        //       'ip' => $request->ip(), 'ua' => $request->userAgent(),
        //   ], null
        // );

        return response()->json(['ok' => true, 'message' => class_basename($class).' restored.']);
    }

        public function data(LogIndexRequest $request)
    {
        $filters = $request->filters();

        // Tabulator uses "size" not "per_page"
        $page = (int) $request->input('page', 1);
        $size = (int) $request->input('size', 20);

        // remove per_page so it doesn't interfere with repo filtering
        unset($filters['per_page']);

        return response()->json(
            $this->auditTable->tableData($filters, $page, $size)
        );
    }
}
