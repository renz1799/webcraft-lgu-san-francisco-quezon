<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsRolePermissionBundles;
use Illuminate\Database\Seeder;

class CoreRolePermissionSeeder extends Seeder
{
    use SeedsRolePermissionBundles;

    private const STAFF_TASK_PERMISSIONS = [
        'tasks.view',
        'tasks.claim',
        'tasks.comment',
        'tasks.update_status',
    ];

    public function run(): void
    {
        $this->syncRoleAliasesToAllModulePermissions('CORE', ['Administrator', 'admin']);
        $this->syncRoleToNamedPermissions('CORE', 'Staff', self::STAFF_TASK_PERMISSIONS);

        $this->command?->info('Core Administrator/admin and Staff task permission bundles synced successfully.');
    }
}
