<?php

namespace App\Builders\Access;

use App\Builders\Contracts\Access\PermissionAuditDisplayBuilderInterface;
use App\Models\Permission;

class PermissionAuditDisplayBuilder implements PermissionAuditDisplayBuilderInterface
{
    public function buildCreatedDisplay(Permission $permission): array
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

    public function buildUpdatedDisplay(array $before, array $after): array
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

    public function buildDeletedDisplay(Permission $permission): array
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

    public function buildRestoredDisplay(Permission $permission): array
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
