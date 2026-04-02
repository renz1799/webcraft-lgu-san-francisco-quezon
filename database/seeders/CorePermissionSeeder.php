<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsModulePermissions;
use Illuminate\Database\Seeder;

class CorePermissionSeeder extends Seeder
{
    use SeedsModulePermissions;

    public function run(): void
    {
        $this->seedGroupedPermissions('CORE', [
            'Core / Users' => [
                'users.view',
                'users.create',
                'users.update',
                'users.deactivate',
                'users.restore',
                'users.reset_password',
                'users.view_access',
                'users.manage_access',
            ],
            'Core / Roles' => [
                'roles.view',
                'roles.create',
                'roles.update',
                'roles.archive',
                'roles.restore',
            ],
            'Core / Permissions' => [
                'permissions.view',
                'permissions.create',
                'permissions.update',
                'permissions.archive',
                'permissions.restore',
            ],
            'Core / Identity Governance' => [
                'identity_change_requests.view',
                'identity_change_requests.approve',
                'identity_change_requests.reject',
            ],
            'Core / Shared Tasks' => [
                'tasks.view',
                'tasks.view_all',
                'tasks.create',
                'tasks.claim',
                'tasks.comment',
                'tasks.update_status',
                'tasks.reassign',
                'tasks.archive',
                'tasks.restore',
            ],
            'Core / Audit and Logs' => [
                'audit_logs.view',
                'audit_logs.print',
                'audit_logs.restore_data',
                'login_logs.view',
            ],
            'Core / Drive' => [
                'drive_connections.view',
                'drive_connections.connect',
                'drive_connections.disconnect',
                'drive_files.create',
            ],
            'Core / Workflow Notifications' => [
                'workflow_notifications.view',
                'workflow_notifications.update',
            ],
            'Core / Theme' => [
                'theme.update_colors',
            ],
            'Core / Shared Reference Data' => [
                'accountable_persons.view',
                'accountable_persons.create',
                'accountable_persons.update',
                'accountable_persons.archive',
                'accountable_persons.restore',
            ],
        ]);

        $this->command?->info('Core normalized permissions seeded successfully.');
    }
}
