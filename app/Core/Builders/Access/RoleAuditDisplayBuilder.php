<?php

namespace App\Core\Builders\Access;

use App\Core\Builders\Contracts\Access\RoleAuditDisplayBuilderInterface;
use App\Core\Models\Role;
use App\Core\Support\PermissionNaming;

class RoleAuditDisplayBuilder implements RoleAuditDisplayBuilderInterface
{
    public function buildCreatedDisplay(Role $role, array $permissions): array
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

    public function buildUpdatedDisplay(array $before, array $after): array
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

    public function buildDeletedDisplay(Role $role, array $snapshot): array
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

    public function buildRestoredDisplay(Role $role, int $permissionCount): array
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
                'Permission Count' => $permissionCount,
            ],
        ];
    }

    private function formatPermissionLabel(string $permission): string
    {
        return PermissionNaming::displayName($permission);
    }
}
