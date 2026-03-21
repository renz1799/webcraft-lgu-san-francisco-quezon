<?php

namespace App\Core\Http\Controllers\AuditLogs;

use App\Http\Controllers\Controller;
use App\Core\Http\Requests\AuditLogs\RestoreSubjectRequest;
use App\Core\Services\Contracts\AuditLogs\AuditRestoreServiceInterface;
use Illuminate\Http\JsonResponse;

class AuditRestoreController extends Controller
{
    public function __construct(
        private readonly AuditRestoreServiceInterface $auditRestoreService,
    ) {}

    public function restore(RestoreSubjectRequest $request): JsonResponse
    {
        return response()->json([
            'ok' => $this->auditRestoreService->restore($request->model()),
        ]);
    }
}
