<?php

namespace App\Services\Access;

use App\Models\Permission;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\Contracts\AuditLogServiceInterface;
use App\Services\Contracts\RoleServiceInterface;

class RoleService implements RoleServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repo,
        private readonly AuditLogServiceInterface $audit,
    ) {}

    public function indexData(): array
    {
        return [
            'permissions' => Permission::query()
                ->orderBy('page')
                ->orderBy('name')
                ->get(),
        ];
    }

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));

        $filters = $params;
        unset($filters['page'], $filters['size']);

        return $this->repo->datatable($filters, $page, $size);
    }

    public function create(array $data): Role
    {
        $role = $this->repo->create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $permIds = array_values($data['permissions'] ?? []);
        $this->repo->syncPermissions($role, $permIds);
        $permissionNames = Permission::whereIn('id', $permIds)->pluck('name')->values()->all();

        $this->audit->record(
            'role.created',
            $role,
            [],
            [
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => $permissionNames,
            ],
            $this->meta(),
            null,
            $this->buildRoleCreatedDisplay($role, $permissionNames)
        );

        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        $before = [
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->values()->all(),
        ];

        $role = $this->repo->update($role, ['name' => $data['name']]);

        $permIds = array_values($data['permissions'] ?? []);
        $this->repo->syncPermissions($role, $permIds);

        $after = [
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->values()->all(),
        ];

        $this->audit->record(
            'role.updated',
            $role,
            $before,
            $after,
            $this->meta(),
            null,
            $this->buildRoleUpdatedDisplay($before, $after)
        );

        return $role;
    }

    public function delete(Role $role): void
    {
        $snapshot = [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->values()->all(),
        ];

        $this->repo->delete($role);

        $this->audit->record(
            'role.deleted',
            $role,
            $snapshot,
            ['deleted_at' => now()->toDateTimeString()],
            $this->meta(),
            null,
            $this->buildRoleDeletedDisplay($role, $snapshot)
        );
    }

    public function restoreRole(string|Role $role): bool
    {
        $model = $role instanceof Role ? $role : $this->repo->findByIdWithTrashed($role);

        if (! $model) {
            return false;
        }

        $deletedAt = $model->deleted_at?->toDateTimeString();
        $ok = $this->repo->restore($model);

        if (! $ok) {
            return false;
        }

        $model->refresh();

        $this->audit->record(
            'role.restored',
            $model,
            ['deleted_at' => $deletedAt],
            ['restored_at' => now()->toDateTimeString()],
            $this->meta(),
            null,
            $this->buildRoleRestoredDisplay($model)
        );

        return true;
    }

    private function meta(): array
    {
        return [
            'ip' => request()->ip(),
            'ua' => request()->userAgent(),
        ];
    }

    private function buildRoleCreatedDisplay(Role $role, array $permissions): array
    {
        return [
            'summary' => 'Role created: ' . $role->name,
            'subject_label' => $role->name,
            'sections' => [
                [
                    'title' => 'Role Details',
                    'items' => [
                        [
                            'label' => 'Role Name',
                            'before' => 'None',
                            'after' => $role->name,
                        ],
                        [
                            'label' => 'Permissions',
                            'value' => array_map([$this, 'formatPermissionLabel'], $permissions),
                        ],
                    ],
                ],
            ],
            'request_details' => [
                'Guard' => $role->guard_name,
                'Permission Count' => count($permissions),
            ],
        ];
    }

    private function buildRoleUpdatedDisplay(array $before, array $after): array
    {
        $beforePerms = $before['permissions'] ?? [];
        $afterPerms = $after['permissions'] ?? [];

        return [
            'summary' => 'Role updated: ' . ($after['name'] ?? $before['name'] ?? 'Role'),
            'subject_label' => $after['name'] ?? $before['name'] ?? 'Role',
            'sections' => [
                [
                    'title' => 'Role Details',
                    'items' => [
                        [
                            'label' => 'Role Name',
                            'before' => $before['name'] ?? 'None',
                            'after' => $after['name'] ?? 'None',
                        ],
                        [
                            'label' => 'Added Permissions',
                            'value' => array_map([$this, 'formatPermissionLabel'], array_values(array_diff($afterPerms, $beforePerms))),
                        ],
                        [
                            'label' => 'Removed Permissions',
                            'value' => array_map([$this, 'formatPermissionLabel'], array_values(array_diff($beforePerms, $afterPerms))),
                        ],
                    ],
                ],
            ],
            'request_details' => [
                'Permission Count' => count($afterPerms),
            ],
        ];
    }

    private function buildRoleDeletedDisplay(Role $role, array $snapshot): array
    {
        return [
            'summary' => 'Role archived: ' . $role->name,
            'subject_label' => $role->name,
            'sections' => [
                [
                    'title' => 'Role Lifecycle',
                    'items' => [
                        [
                            'label' => 'Archive Status',
                            'before' => 'Active Record',
                            'after' => 'Archived',
                        ],
                    ],
                ],
            ],
            'request_details' => [
                'Permission Count' => count($snapshot['permissions'] ?? []),
            ],
        ];
    }

    private function buildRoleRestoredDisplay(Role $role): array
    {
        return [
            'summary' => 'Role restored: ' . $role->name,
            'subject_label' => $role->name,
            'sections' => [
                [
                    'title' => 'Role Lifecycle',
                    'items' => [
                        [
                            'label' => 'Archive Status',
                            'before' => 'Archived',
                            'after' => 'Active Record',
                        ],
                    ],
                ],
            ],
            'request_details' => [
                'Permission Count' => $role->permissions()->count(),
            ],
        ];
    }

    private function formatPermissionLabel(string $permission): string
    {
        return str($permission)
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->title()
            ->value();
    }
}

