<?php

namespace Tests\Feature;

use App\Core\Models\Permission;
use App\Core\Models\Role;
use App\Core\Models\User;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Services\Access\UserAccessService;
use App\Core\Services\Contracts\Access\RoleAssignments\ModuleRoleAssignmentServiceInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Support\AdminRouteResolver;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class UserAccessServiceModuleScopeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_get_user_permissions_returns_current_module_data_only(): void
    {
        $resolver = Mockery::mock(AdminRouteResolver::class);
        $resolver->shouldReceive('isModuleScoped')->andReturn(false);
        $this->app->instance(AdminRouteResolver::class, $resolver);

        $user = User::query()->create([
            'id' => 'user-1',
            'username' => 'staff.user',
            'email' => 'staff@example.com',
            'password' => 'secret',
            'is_active' => true,
            'user_type' => 'Viewer',
        ]);

        $currentRole = Role::query()->create([
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

        $currentPermission = Permission::query()->create([
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

        DB::table('model_has_permissions')->insert([
            'module_id' => 'module-1',
            'permission_id' => 'permission-1',
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        DB::table('model_has_permissions')->insert([
            'module_id' => 'module-2',
            'permission_id' => 'permission-2',
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        $repo = Mockery::mock(UserRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);
        $roleAssignments = Mockery::mock(ModuleRoleAssignmentServiceInterface::class);

        $context->shouldReceive('moduleId')->atLeast()->once()->andReturn('module-1');
        $roleAssignments->shouldReceive('roles')->atLeast()->once()->with($user)->andReturn(collect([$currentRole]));

        $service = new UserAccessService($repo, $audit, $context, $roleAssignments);

        $result = $service->getUserPermissions($user);

        $this->assertSame(['view Tasks'], $result['userPermissions']);
        $this->assertSame(['Staff'], $result['roles']);
        $this->assertSame('Staff', $result['currentRole']);
        $this->assertTrue($result['permissions']->has('Tasks'));
        $this->assertFalse($result['permissions']->has('Inspections'));
        $this->assertSame([$currentPermission->name], $result['permissions']['Tasks']->pluck('name')->all());
    }

    public function test_get_user_permissions_rejects_users_outside_module_scope(): void
    {
        $resolver = Mockery::mock(AdminRouteResolver::class);
        $resolver->shouldReceive('isModuleScoped')->andReturn(true);
        $this->app->instance(AdminRouteResolver::class, $resolver);

        $user = User::query()->create([
            'id' => 'user-1',
            'username' => 'staff.user',
            'email' => 'staff@example.com',
            'password' => 'secret',
            'is_active' => true,
            'user_type' => 'Viewer',
        ]);

        Role::query()->create([
            'id' => 'role-1',
            'module_id' => 'module-1',
            'name' => 'Staff',
            'guard_name' => 'web',
        ]);

        DB::table('user_modules')->insert([
            'id' => 'user-module-1',
            'user_id' => (string) $user->id,
            'module_id' => 'module-2',
            'is_active' => true,
        ]);

        $repo = Mockery::mock(UserRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);
        $roleAssignments = Mockery::mock(ModuleRoleAssignmentServiceInterface::class);

        $context->shouldReceive('moduleId')->atLeast()->once()->andReturn('module-1');
        $roleAssignments->shouldReceive('roles')->never();

        $service = new UserAccessService($repo, $audit, $context, $roleAssignments);

        $this->expectException(ModelNotFoundException::class);

        $service->getUserPermissions($user);
    }

    public function test_get_edit_data_includes_role_defaults_for_current_module(): void
    {
        $resolver = Mockery::mock(AdminRouteResolver::class);
        $resolver->shouldReceive('isModuleScoped')->andReturn(false);
        $this->app->instance(AdminRouteResolver::class, $resolver);

        $user = User::query()->create([
            'id' => 'user-edit-1',
            'username' => 'gso.staff',
            'email' => 'gso.staff@example.com',
            'password' => 'secret',
            'is_active' => true,
            'user_type' => 'Viewer',
        ]);

        $staffRole = Role::query()->create([
            'id' => 'role-staff',
            'module_id' => 'module-1',
            'name' => 'Staff',
            'guard_name' => 'web',
        ]);

        Role::query()->create([
            'id' => 'role-other',
            'module_id' => 'module-2',
            'name' => 'Other Module Role',
            'guard_name' => 'web',
        ]);

        $viewPermission = Permission::query()->create([
            'id' => 'permission-view',
            'module_id' => 'module-1',
            'name' => 'view Tasks',
            'page' => 'Tasks',
            'guard_name' => 'web',
        ]);

        $editPermission = Permission::query()->create([
            'id' => 'permission-edit',
            'module_id' => 'module-1',
            'name' => 'edit Tasks',
            'page' => 'Tasks',
            'guard_name' => 'web',
        ]);

        $otherPermission = Permission::query()->create([
            'id' => 'permission-other',
            'module_id' => 'module-2',
            'name' => 'view Inspections',
            'page' => 'Inspections',
            'guard_name' => 'web',
        ]);

        DB::table('role_has_permissions')->insert([
            ['permission_id' => (string) $viewPermission->id, 'role_id' => (string) $staffRole->id],
            ['permission_id' => (string) $editPermission->id, 'role_id' => (string) $staffRole->id],
            ['permission_id' => (string) $otherPermission->id, 'role_id' => (string) $staffRole->id],
        ]);

        $repo = Mockery::mock(UserRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);
        $roleAssignments = Mockery::mock(ModuleRoleAssignmentServiceInterface::class);

        $context->shouldReceive('moduleId')->atLeast()->once()->andReturn('module-1');
        $roleAssignments->shouldReceive('roles')->atLeast()->once()->with($user)->andReturn(collect([$staffRole]));

        $service = new UserAccessService($repo, $audit, $context, $roleAssignments);

        $result = $service->getEditData($user);

        $this->assertArrayHasKey('roleDefaults', $result);
        $this->assertSame([
            'Staff' => [
                'Tasks' => [
                    'Tasks' => ['view', 'modify'],
                ],
            ],
        ], $result['roleDefaults']);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('must_change_password')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('user_type')->default('Viewer');
            $table->timestamps();
            $table->softDeletes();
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

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->uuid('module_id');
            $table->uuid('permission_id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->primary(['module_id', 'permission_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->uuid('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('user_modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('module_id');
            $table->boolean('is_active')->default(true);
        });
    }
}
