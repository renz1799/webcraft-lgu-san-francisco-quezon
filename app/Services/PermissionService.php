<?php

namespace App\Services;

use App\Models\Permission;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Services\Contracts\AuditLogServiceInterface;
use App\Services\Contracts\PermissionServiceInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class PermissionService implements PermissionServiceInterface
{
    public function __construct(
        private readonly PermissionRepositoryInterface $repo,
        private readonly AuditLogServiceInterface $audit,
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));

        $filters = $params;
        unset($filters['page'], $filters['size']);

        return $this->repo->datatable($filters, $page, $size);
    }

    public function create(array $data): Permission
    {
        $payload = [
            'guard_name' => $data['guard_name'] ?? 'web',
            'name' => trim($data['name']),
            'page' => trim($data['page']),
        ];

        $permission = $this->repo->create($payload);

        $this->safeAudit(
            action: 'permission.created',
            permission: $permission,
            old: null,
            new: Arr::only($permission->toArray(), ['id', 'name', 'page', 'guard_name'])
        );

        return $permission;
    }

    public function update(Permission $permission, array $data): Permission
    {
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
            new: $after
        );

        return $updated;
    }

    public function delete(Permission $permission): void
    {
        $snapshot = Arr::only($permission->toArray(), ['id', 'name', 'page', 'guard_name']);

        $this->repo->delete($permission);

        $this->safeAudit(
            action: 'permission.deleted',
            permission: $permission,
            old: $snapshot,
            new: ['deleted_at' => now()->toDateTimeString()]
        );
    }

    public function restorePermission(string|Permission $permission): bool
    {
        $model = $permission instanceof Permission
            ? $permission
            : $this->repo->findByIdWithTrashed($permission);

        if (! $model) {
            return false;
        }

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
            new: ['restored_at' => now()->toDateTimeString()]
        );

        return true;
    }

    private function safeAudit(string $action, Permission $permission, ?array $old, ?array $new): void
    {
        try {
            $this->audit->record(
                $action,
                $permission,
                $old ?? [],
                $new ?? [],
                [
                    'ip' => request()->ip(),
                    'ua' => request()->userAgent(),
                ],
                null
            );
        } catch (\Throwable $e) {
            Log::warning('audit.record_failed', [
                'action' => $action,
                'target' => ['type' => Permission::class, 'id' => $permission->id],
                'error' => $e->getMessage(),
            ]);
        }
    }
}
