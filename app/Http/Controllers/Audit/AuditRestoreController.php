<?php

namespace App\Http\Controllers\Audit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logs\RestoreSubjectRequest;
use App\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AuditRestoreController extends Controller
{
    public function __construct(
        private readonly AuditLogServiceInterface $audit
    ) {
        // auth/role guard is already handled in the FormRequest::authorize()
    }

    public function restore(RestoreSubjectRequest $request): JsonResponse
    {
        // The FormRequest already validates & resolves the soft-deleted model
        $model   = $request->model();
        $before  = ['deleted_at' => optional($model->deleted_at)->toDateTimeString()];

        // Perform restore
        $model->restore();

        // Build audit payload
        $after   = ['deleted_at' => null];
        $type    = class_basename($model);                // e.g. "User", "Permission", "Role"
        $action  = strtolower($type) . '.restored';       // e.g. "user.restored"

        // Record audit (don’t block on failures)
        try {
            $this->audit->record(
                $action,
                $model,                                    // subject
                $before,                                   // changes_old
                $after,                                    // changes_new
                [
                    'ip'    => $request->ip(),
                    'ua'    => $request->userAgent(),
                    'source'=> 'audit.restore.endpoint',
                ],
                null
            );
        } catch (\Throwable $e) {
            Log::warning('audit.record_failed', [
                'action' => $action,
                'target' => ['type' => get_class($model), 'id' => $model->getKey()],
                'error'  => $e->getMessage(),
            ]);
        }

        return response()->json(['ok' => true]);
    }
}


