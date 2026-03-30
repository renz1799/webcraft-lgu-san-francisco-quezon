<?php

namespace Database\Seeders\Modules\GSO;

use Database\Seeders\Concerns\SeedsRolePermissionBundles;
use Illuminate\Database\Seeder;

class GsoRolePermissionSeeder extends Seeder
{
    use SeedsRolePermissionBundles;

    private const STAFF_TASK_PERMISSIONS = [
        'tasks.view',
        'tasks.claim',
        'tasks.comment',
        'tasks.update_status',
    ];

    private const STAFF_DOCUMENT_PERMISSIONS = [
        'par.view',
        'par.create',
        'par.update',
        'par.submit',
        'par.finalize',
        'par.reopen',
        'par.archive',
        'par.manage_items',
        'par.print',
        'ics.view',
        'ics.create',
        'ics.update',
        'ics.submit',
        'ics.finalize',
        'ics.reopen',
        'ics.archive',
        'ics.manage_items',
        'ics.print',
        'ptr.view',
        'ptr.create',
        'ptr.update',
        'ptr.submit',
        'ptr.finalize',
        'ptr.reopen',
        'ptr.archive',
        'ptr.manage_items',
        'ptr.print',
        'itr.view',
        'itr.create',
        'itr.update',
        'itr.submit',
        'itr.finalize',
        'itr.reopen',
        'itr.archive',
        'itr.manage_items',
        'itr.print',
        'wmr.view',
        'wmr.create',
        'wmr.update',
        'wmr.submit',
        'wmr.approve',
        'wmr.finalize',
        'wmr.reopen',
        'wmr.archive',
        'wmr.manage_items',
        'wmr.print',
    ];

    public function run(): void
    {
        $this->syncRoleAliasesToAllModulePermissions('GSO', ['Administrator', 'admin']);
        $this->syncRoleToNamedPermissions(
            'GSO',
            'Staff',
            array_values(array_unique([
                ...self::STAFF_TASK_PERMISSIONS,
                ...self::STAFF_DOCUMENT_PERMISSIONS,
            ]))
        );

        $this->command?->info('GSO Administrator/admin and Staff permission bundles synced successfully.');
    }
}
