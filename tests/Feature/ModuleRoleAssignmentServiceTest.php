<?php

namespace Tests\Feature;

use App\Core\Models\Module;
use App\Core\Models\Permission;
use App\Core\Models\Role;
use App\Core\Models\User;
use App\Core\Models\UserModule;
use App\Core\Services\Access\RoleAssignments\ModuleRoleAssignmentService;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ModuleRoleAssignmentServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        parent::tearDown();
    }

    public function test_assign_syncs_module_role_and_module_permissions(): void
    {
        $module = Module::query()->create([
            'id' => 'module-1',
            'code' => 'OPS',
            'name' => 'Operations',
            'is_active' => true,
        ]);

        config()->set('module.id', $module->id);

        $user = User::query()->create([
            'id' => 'user-1',
            'username' => 'new.user',
            'email' => 'new.user@example.com',
            'password' => 'secret',
            'user_type' => 'Viewer',
            'is_active' => true,
        ]);

        UserModule::query()->create([
            'id' => 'user-module-1',
            'user_id' => $user->id,
            'module_id' => $module->id,
            'department_id' => null,
            'is_active' => true,
        ]);

        $role = Role::query()->create([
            'id' => 'role-1',
            'module_id' => $module->id,
            'name' => 'Staff',
            'guard_name' => 'web',
        ]);

        $permission = Permission::query()->create([
            'id' => 'permission-1',
            'module_id' => $module->id,
            'name' => 'view Tasks',
            'page' => 'Tasks',
            'guard_name' => 'web',
        ]);

        DB::table('role_has_permissions')->insert([
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);

        $service = new ModuleRoleAssignmentService(new CurrentContext());

        $service->assign($user, $role);

        $this->assertDatabaseHas('model_has_roles', [
            'module_id' => $module->id,
            'role_id' => $role->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        $this->assertDatabaseHas('model_has_permissions', [
            'module_id' => $module->id,
            'permission_id' => $permission->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('primary_department_id')->nullable();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('must_change_password')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('user_type')->default('Viewer');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('module_id');
            $table->uuid('department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

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

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->uuid('module_id');
            $table->uuid('role_id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->primary(['module_id', 'role_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->uuid('module_id');
            $table->uuid('permission_id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->primary(['module_id', 'permission_id', 'model_id', 'model_type']);
        });
    }
}
