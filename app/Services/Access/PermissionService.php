<?php

namespace App\Services\Access;

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
            new: Arr::only($permission->toArray(), ['id', 'name', 'page', 'guard_name']),
            display: $this->buildPermissionCreatedDisplay($permission)
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
            new: $after,
            display: $this->buildPermissionUpdatedDisplay($before, $after)
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
            new: ['deleted_at' => now()->toDateTimeString()],
            display: $this->buildPermissionDeletedDisplay($permission)
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
            new: ['restored_at' => now()->toDateTimeString()],
            display: $this->buildPermissionRestoredDisplay($model)
        );

        return true;
    }

    private function safeAudit(string $action, Permission $permission, ?array $old, ?array $new, array $display = []): void
    {
        try {
            $this->audit->record(
                $action,
                $permission,
                $old ?? [],
                $new ?? [],
                $this->meta(),
                null,
                $display
            );
        } catch (\Throwable $e) {
            Log::warning('audit.record_failed', [
                'action' => $action,
                'target' => ['type' => Permission::class, 'id' => $permission->id],
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function meta(): array
    {
        return [
            'ip' => request()->ip(),
            'ua' => request()->userAgent(),
        ];
    }

    private function buildPermissionCreatedDisplay(Permission $permission): array
    {
        return [
            'summary' => 'Permission created: ' . $this->permissionDisplayName($permission->name),
            'subject_label' => $this->permissionDisplayName($permission->name),
            'sections' => [
                [
                    'title' => 'Permission Details',
                    'items' => [
                        [
                            'label' => 'Permission Name',
                            'before' => 'None',
                            'after' => $this->permissionDisplayName($permission->name),
                        ],
                        [
                            'label' => 'Page',
                            'before' => 'None',
                            'after' => $this->pageDisplayName($permission->page),
                        ],
                    ],
                ],
            ],
            'request_details' => [
                'Guard' => $permission->guard_name,
            ],
        ];
    }

    private function buildPermissionUpdatedDisplay(array $before, array $after): array
    {
        return [
            'summary' => 'Permission updated: ' . $this->permissionDisplayName($after['name'] ?? $before['name'] ?? 'Permission'),
            'subject_label' => $this->permissionDisplayName($after['name'] ?? $before['name'] ?? 'Permission'),
            'sections' => [
                [
                    'title' => 'Permission Details',
                    'items' => [
                        [
                            'label' => 'Permission Name',
                            'before' => $this->permissionDisplayName($before['name'] ?? 'None'),
                            'after' => $this->permissionDisplayName($after['name'] ?? 'None'),
                        ],
                        [
                            'label' => 'Page',
                            'before' => $this->pageDisplayName($before['page'] ?? 'None'),
                            'after' => $this->pageDisplayName($after['page'] ?? 'None'),
                        ],
                        [
                            'label' => 'Guard',
                            'before' => $before['guard_name'] ?? 'None',
                            'after' => $after['guard_name'] ?? 'None',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildPermissionDeletedDisplay(Permission $permission): array
    {
        return [
            'summary' => 'Permission archived: ' . $this->permissionDisplayName($permission->name),
            'subject_label' => $this->permissionDisplayName($permission->name),
            'sections' => [
                [
                    'title' => 'Permission Lifecycle',
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
                'Page' => $this->pageDisplayName($permission->page),
            ],
        ];
    }

    private function buildPermissionRestoredDisplay(Permission $permission): array
    {
        return [
            'summary' => 'Permission restored: ' . $this->permissionDisplayName($permission->name),
            'subject_label' => $this->permissionDisplayName($permission->name),
            'sections' => [
                [
                    'title' => 'Permission Lifecycle',
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
                'Page' => $this->pageDisplayName($permission->page),
            ],
        ];
    }

    private function permissionDisplayName(string $name): string
    {
        return str($name)
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->title()
            ->value();
    }

    private function pageDisplayName(string $page): string
    {
        return str($page)
            ->replaceMatches('/[_\s]+/', ' ')
            ->trim()
            ->title()
            ->value();
    }
}

