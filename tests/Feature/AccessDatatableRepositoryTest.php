<?php

namespace Tests\Feature;

use App\Core\Models\Permission;
use App\Core\Models\Role;
use App\Core\Repositories\Eloquent\EloquentPermissionRepository;
use App\Core\Repositories\Eloquent\EloquentRoleRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AccessDatatableRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->registerRoutes();
        $this->createSchema();
    }

    public function test_permission_datatable_loads_module_roles_without_type_error(): void
    {
        $permission = Permission::query()->create([
            'id' => 'permission-1',
            'module_id' => 'module-1',
            'name' => 'view Tasks',
            'page' => 'Tasks',
            'guard_name' => 'web',
        ]);

        $role = Role::query()->create([
            'id' => 'role-1',
            'module_id' => 'module-1',
            'name' => 'Staff',
            'guard_name' => 'web',
        ]);

        Role::query()->create([
            'id' => 'role-2',
            'module_id' => 'module-2',
            'name' => 'Inspector',
            'guard_name' => 'web',
        ]);

        DB::table('role_has_permissions')->insert([
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);

        $result = (new EloquentPermissionRepository())->datatable('module-1', [], 1, 15);

        $this->assertSame(1, $result['total']);
        $this->assertSame('Staff', $result['data'][0]['roles_preview']);
        $this->assertSame(1, $result['data'][0]['roles_count']);
    }

    public function test_role_datatable_loads_module_permissions_without_type_error(): void
    {
        $role = Role::query()->create([
            'id' => 'role-1',
            'module_id' => 'module-1',
            'name' => 'Staff',
            'guard_name' => 'web',
        ]);

        $permission = Permission::query()->create([
            'id' => 'permission-1',
            'module_id' => 'module-1',
            'name' => 'view Tasks',
            'page' => 'Tasks',
            'guard_name' => 'web',
        ]);

        Permission::query()->create([
            'id' => 'permission-2',
            'module_id' => 'module-2',
            'name' => 'view Inspections',
            'page' => 'Inspections',
            'guard_name' => 'web',
        ]);

        DB::table('role_has_permissions')->insert([
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);

        $result = (new EloquentRoleRepository())->datatable('module-1', [], 1, 15);

        $this->assertSame(1, $result['total']);
        $this->assertSame(1, $result['data'][0]['permissions_count']);
        $this->assertSame(['permission-1'], $result['data'][0]['permission_ids']);
    }

    private function registerRoutes(): void
    {
        Route::get('/test/permissions/{permission}', fn () => 'ok')->name('access.permissions.update');
        Route::delete('/test/permissions/{permission}', fn () => 'ok')->name('access.permissions.destroy');
        Route::post('/test/permissions/{permission}/restore', fn () => 'ok')->name('access.permissions.restore');

        Route::put('/test/roles/{role}', fn () => 'ok')->name('access.roles.update');
        Route::delete('/test/roles/{role}', fn () => 'ok')->name('access.roles.destroy');
        Route::post('/test/roles/{role}/restore', fn () => 'ok')->name('access.roles.restore');
    }

    private function createSchema(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->string('name');
            $table->string('page')->nullable();
            $table->string('guard_name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->uuid('role_id');
            $table->primary(['permission_id', 'role_id']);
        });
    }
}
