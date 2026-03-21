<?php

namespace App\Core\Services\Access;

use App\Core\Builders\Contracts\Access\RoleAuditDisplayBuilderInterface;
use App\Core\Models\Permission;
use App\Core\Models\Role;
use App\Core\Repositories\Contracts\RoleRepositoryInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\Access\RoleServiceInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class RoleService implements RoleServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repo,
        private readonly AuditLogServiceInterface $audit,
        private readonly CurrentContext $context,
        private readonly RoleAuditDisplayBuilderInterface $auditDisplayBuilder,
    ) {}

    public function indexData(): array
    {
        $moduleId = $this->requireModuleId();

        return [
            'permissions' => Permission::query()
                ->where('module_id', $moduleId)
                ->where('guard_name', 'web')
                ->whereNull('deleted_at')
                ->orderBy('page')
                ->orderBy('name')
                ->get(),
        ];
    }

    public function datatable(array $params): array
    {
        $moduleId = $this->requireModuleId();
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));

        $filters = $params;
        unset($filters['page'], $filters['size']);

        return $this->repo->datatable($moduleId, $filters, $page, $size);
    }

    public function create(array $data): Role
    {
        $moduleId = $this->requireModuleId();
        $permissionIds = $this->normalizePermissionIds($data['permissions'] ?? []);
        $permissionNames = $this->permissionNamesForCurrentModule($moduleId, $permissionIds);

        $role = $this->repo->create($moduleId, [
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $this->repo->syncPermissions($role, $moduleId, $permissionIds);

        $this->audit->record(
            action: 'role.created',
            subject: $role,
            changesOld: [],
            changesNew: [
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => $permissionNames,
            ],
            display: $this->auditDisplayBuilder->buildCreatedDisplay($role, $permissionNames)
        );

        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        $moduleId = $this->requireModuleId();
        $this->ensureRoleBelongsToCurrentModule($role, $moduleId);

        $before = [
            'name' => $role->name,
            'permissions' => $role->permissions()
                ->where('permissions.module_id', $moduleId)
                ->pluck('permissions.name')
                ->values()
                ->all(),
        ];

        $role = $this->repo->update($role, ['name' => $data['name']]);

        $permissionIds = $this->normalizePermissionIds($data['permissions'] ?? []);
        $this->permissionNamesForCurrentModule($moduleId, $permissionIds);
        $this->repo->syncPermissions($role, $moduleId, $permissionIds);

        $after = [
            'name' => $role->name,
            'permissions' => $role->permissions()
                ->where('permissions.module_id', $moduleId)
                ->pluck('permissions.name')
                ->values()
                ->all(),
        ];

        $this->audit->record(
            action: 'role.updated',
            subject: $role,
            changesOld: $before,
            changesNew: $after,
            display: $this->auditDisplayBuilder->buildUpdatedDisplay($before, $after)
        );

        return $role;
    }

    public function delete(Role $role): void
    {
        $moduleId = $this->requireModuleId();
        $this->ensureRoleBelongsToCurrentModule($role, $moduleId);

        $snapshot = [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions()
                ->where('permissions.module_id', $moduleId)
                ->pluck('permissions.name')
                ->values()
                ->all(),
        ];

        $this->repo->delete($role);

        $this->audit->record(
            action: 'role.deleted',
            subject: $role,
            changesOld: $snapshot,
            changesNew: ['deleted_at' => now()->toDateTimeString()],
            display: $this->auditDisplayBuilder->buildDeletedDisplay($role, $snapshot)
        );
    }

    public function restoreRole(string|Role $role): bool
    {
        $moduleId = $this->requireModuleId();
        $model = $role instanceof Role ? $role : $this->repo->findByIdWithTrashed($moduleId, $role);

        if (! $model) {
            return false;
        }

        $this->ensureRoleBelongsToCurrentModule($model, $moduleId);

        $deletedAt = $model->deleted_at?->toDateTimeString();
        $ok = $this->repo->restore($model);

        if (! $ok) {
            return false;
        }

        $model->refresh();

        $this->audit->record(
            action: 'role.restored',
            subject: $model,
            changesOld: ['deleted_at' => $deletedAt],
            changesNew: ['restored_at' => now()->toDateTimeString()],
            display: $this->auditDisplayBuilder->buildRestoredDisplay(
                $model,
                $model->permissions()
                    ->where('permissions.module_id', $moduleId)
                    ->count()
            )
        );

        return true;
    }

    private function requireModuleId(): string
    {
        $moduleId = $this->context->moduleId();

        if (! $moduleId) {
            throw new \RuntimeException('Current module context is not available.');
        }

        return $moduleId;
    }

    private function ensureRoleBelongsToCurrentModule(Role $role, string $moduleId): void
    {
        if ($role->module_id === $moduleId) {
            return;
        }

        throw (new ModelNotFoundException())->setModel(Role::class, [$role->getKey()]);
    }

    private function normalizePermissionIds(array $permissionIds): array
    {
        return array_values(array_unique(array_filter(
            array_map(static fn (mixed $permissionId): string => trim((string) $permissionId), $permissionIds)
        )));
    }

    private function permissionNamesForCurrentModule(string $moduleId, array $permissionIds): array
    {
        if ($permissionIds === []) {
            return [];
        }

        $permissionNames = Permission::query()
            ->where('module_id', $moduleId)
            ->where('guard_name', 'web')
            ->whereNull('deleted_at')
            ->whereIn('id', $permissionIds)
            ->pluck('name')
            ->values()
            ->all();

        if (count($permissionNames) !== count($permissionIds)) {
            throw ValidationException::withMessages([
                'permissions' => 'Selected permissions must belong to the current module.',
            ]);
        }

        return $permissionNames;
    }
}
