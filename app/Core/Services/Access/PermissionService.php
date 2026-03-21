<?php

namespace App\Core\Services\Access;

use App\Core\Builders\Contracts\Access\PermissionAuditDisplayBuilderInterface;
use App\Core\Models\Permission;
use App\Core\Repositories\Contracts\PermissionRepositoryInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\Access\PermissionServiceInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class PermissionService implements PermissionServiceInterface
{
    public function __construct(
        private readonly PermissionRepositoryInterface $repo,
        private readonly AuditLogServiceInterface $audit,
        private readonly CurrentContext $context,
        private readonly PermissionAuditDisplayBuilderInterface $auditDisplayBuilder,
    ) {}

    public function datatable(array $params): array
    {
        $moduleId = $this->requireModuleId();
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));

        $filters = $params;
        unset($filters['page'], $filters['size']);

        return $this->repo->datatable($moduleId, $filters, $page, $size);
    }

    public function create(array $data): Permission
    {
        $moduleId = $this->requireModuleId();
        $payload = [
            'guard_name' => $data['guard_name'] ?? 'web',
            'name' => trim($data['name']),
            'page' => trim($data['page']),
        ];

        $permission = $this->repo->create($moduleId, $payload);

        $this->safeAudit(
            action: 'permission.created',
            permission: $permission,
            old: null,
            new: Arr::only($permission->toArray(), ['id', 'name', 'page', 'guard_name']),
            display: $this->auditDisplayBuilder->buildCreatedDisplay($permission)
        );

        return $permission;
    }

    public function update(Permission $permission, array $data): Permission
    {
        $moduleId = $this->requireModuleId();
        $this->ensurePermissionBelongsToCurrentModule($permission, $moduleId);
        $before = Arr::only($permission->toArray(), ['id', 'name', 'page', 'guard_name']);

        $updated = $this->repo->update($permission, [
            'name' => trim($data['name']),
            'page' => trim($data['page']),
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        $after = Arr::only($updated->toArray(), ['id', 'name', 'page', 'guard_name']);

        $this->safeAudit(
            action: 'permission.updated',
            permission: $updated,
            old: $before,
            new: $after,
            display: $this->auditDisplayBuilder->buildUpdatedDisplay($before, $after)
        );

        return $updated;
    }

    public function delete(Permission $permission): void
    {
        $moduleId = $this->requireModuleId();
        $this->ensurePermissionBelongsToCurrentModule($permission, $moduleId);
        $snapshot = Arr::only($permission->toArray(), ['id', 'name', 'page', 'guard_name']);

        $this->repo->delete($permission);

        $this->safeAudit(
            action: 'permission.deleted',
            permission: $permission,
            old: $snapshot,
            new: ['deleted_at' => now()->toDateTimeString()],
            display: $this->auditDisplayBuilder->buildDeletedDisplay($permission)
        );
    }

    public function restorePermission(string|Permission $permission): bool
    {
        $moduleId = $this->requireModuleId();
        $model = $permission instanceof Permission
            ? $permission
            : $this->repo->findByIdWithTrashed($moduleId, $permission);

        if (! $model) {
            return false;
        }

        $this->ensurePermissionBelongsToCurrentModule($model, $moduleId);

        $deletedAt = $model->deleted_at?->toDateTimeString();
        $ok = $this->repo->restore($model);

        if (! $ok) {
            return false;
        }

        $model->refresh();

        $this->safeAudit(
            action: 'permission.restored',
            permission: $model,
            old: ['deleted_at' => $deletedAt],
            new: ['restored_at' => now()->toDateTimeString()],
            display: $this->auditDisplayBuilder->buildRestoredDisplay($model)
        );

        return true;
    }

    private function safeAudit(string $action, Permission $permission, ?array $old, ?array $new, array $display = []): void
    {
        try {
            $this->audit->record(
                action: $action,
                subject: $permission,
                changesOld: $old ?? [],
                changesNew: $new ?? [],
                display: $display
            );
        } catch (\Throwable $e) {
            Log::warning('audit.record_failed', [
                'action' => $action,
                'target' => ['type' => Permission::class, 'id' => $permission->id],
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function requireModuleId(): string
    {
        $moduleId = $this->context->moduleId();

        if (! $moduleId) {
            throw new \RuntimeException('Current module context is not available.');
        }

        return $moduleId;
    }

    private function ensurePermissionBelongsToCurrentModule(Permission $permission, string $moduleId): void
    {
        if ($permission->module_id === $moduleId) {
            return;
        }

        throw (new ModelNotFoundException())->setModel(Permission::class, [$permission->getKey()]);
    }
}
