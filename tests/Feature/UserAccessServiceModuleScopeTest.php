<?php

namespace Tests\Feature;

use App\Core\Builders\Contracts\User\UserPlatformAccessOverviewBuilderInterface;
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
            'name' => 'tasks.view',
            'page' => 'Tasks',
            'guard_name' => 'web',
        ]);

        Permission::query()->create([
            'id' => 'permission-2',
            'module_id' => 'module-2',
            'name' => 'inspections.view',
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

        $service = $this->makeService($repo, $audit, $context, $roleAssignments);

        $result = $service->getUserPermissions($user);

        $this->assertSame(['tasks.view'], $result['userPermissions']);
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

        $service = $this->makeService($repo, $audit, $context, $roleAssignments);

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
            'name' => 'tasks.view',
            'page' => 'Tasks',
            'guard_name' => 'web',
        ]);

        $editPermission = Permission::query()->create([
            'id' => 'permission-edit',
            'module_id' => 'module-1',
            'name' => 'tasks.update',
            'page' => 'Tasks',
            'guard_name' => 'web',
        ]);

        $otherPermission = Permission::query()->create([
            'id' => 'permission-other',
            'module_id' => 'module-2',
            'name' => 'inspections.view',
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

        $service = $this->makeService($repo, $audit, $context, $roleAssignments);

        $result = $service->getEditData($user);

        $this->assertArrayHasKey('roleDefaults', $result);
        $this->assertSame([
            'Staff' => [
                'Tasks' => [
                    'tasks' => ['view', 'update'],
                ],
            ],
        ], $result['roleDefaults']);
    }

    public function test_update_module_status_updates_user_module_membership_without_touching_global_user_status(): void
    {
        $resolver = Mockery::mock(AdminRouteResolver::class);
        $resolver->shouldReceive('isModuleScoped')->andReturn(true);
        $this->app->instance(AdminRouteResolver::class, $resolver);

        $user = User::query()->create([
            'id' => 'user-status-1',
            'username' => 'gso.staff',
            'email' => 'gso.staff@example.com',
            'password' => 'secret',
            'is_active' => true,
            'user_type' => 'Viewer',
        ]);

        DB::table('user_modules')->insert([
            'id' => 'user-module-status-1',
            'user_id' => (string) $user->id,
            'module_id' => 'module-1',
            'is_active' => true,
            'granted_at' => '2026-03-21 08:00:00',
            'revoked_at' => null,
        ]);

        $repo = Mockery::mock(UserRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);
        $roleAssignments = Mockery::mock(ModuleRoleAssignmentServiceInterface::class);

        $context->shouldReceive('moduleId')->atLeast()->once()->andReturn('module-1');
        $context->shouldReceive('module')->andReturn(null);
        $context->shouldReceive('moduleCode')->andReturn('GSO');
        $audit->shouldReceive('record')->once()->withArgs(function (
            string $event,
            User $subject,
            array $before,
            array $after
        ) use ($user): bool {
            return $event === 'user.module_access.status.updated'
                && $subject->is($user)
                && $before['is_active'] === true
                && $after['is_active'] === false;
        });
        $roleAssignments->shouldReceive('roles')->atLeast()->once()->with($user)->andReturn(collect());

        $service = $this->makeService($repo, $audit, $context, $roleAssignments);

        $service->updateModuleStatus($user, false);

        $membership = DB::table('user_modules')
            ->where('user_id', (string) $user->id)
            ->where('module_id', 'module-1')
            ->first();

        $this->assertFalse((bool) $membership->is_active);
        $this->assertNotNull($membership->revoked_at);
        $this->assertSame('2026-03-21 08:00:00', $membership->granted_at);
        $this->assertTrue((bool) $user->fresh()->is_active);
    }

    public function test_update_module_status_can_reenable_inactive_assignment(): void
    {
        $resolver = Mockery::mock(AdminRouteResolver::class);
        $resolver->shouldReceive('isModuleScoped')->andReturn(true);
        $this->app->instance(AdminRouteResolver::class, $resolver);

        $user = User::query()->create([
            'id' => 'user-status-2',
            'username' => 'gso.staff.reenable',
            'email' => 'gso.staff.reenable@example.com',
            'password' => 'secret',
            'is_active' => true,
            'user_type' => 'Viewer',
        ]);

        DB::table('user_modules')->insert([
            'id' => 'user-module-status-2',
            'user_id' => (string) $user->id,
            'module_id' => 'module-1',
            'is_active' => false,
            'granted_at' => '2026-03-21 08:00:00',
            'revoked_at' => '2026-03-21 12:00:00',
        ]);

        $repo = Mockery::mock(UserRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);
        $roleAssignments = Mockery::mock(ModuleRoleAssignmentServiceInterface::class);

        $context->shouldReceive('moduleId')->atLeast()->once()->andReturn('module-1');
        $context->shouldReceive('module')->andReturn(null);
        $context->shouldReceive('moduleCode')->andReturn('GSO');
        $audit->shouldReceive('record')->once()->withArgs(function (
            string $event,
            User $subject,
            array $before,
            array $after
        ) use ($user): bool {
            return $event === 'user.module_access.status.updated'
                && $subject->is($user)
                && $before['is_active'] === false
                && $after['is_active'] === true;
        });
        $roleAssignments->shouldReceive('roles')->atLeast()->once()->with($user)->andReturn(collect());

        $service = $this->makeService($repo, $audit, $context, $roleAssignments);

        $service->updateModuleStatus($user, true);

        $membership = DB::table('user_modules')
            ->where('user_id', (string) $user->id)
            ->where('module_id', 'module-1')
            ->first();

        $this->assertTrue((bool) $membership->is_active);
        $this->assertNotNull($membership->granted_at);
        $this->assertNull($membership->revoked_at);
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

        Schema::create('user_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name_extension')->nullable();
            $table->string('full_name')->nullable();
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
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
        });
    }

    private function makeService(
        UserRepositoryInterface $repo,
        AuditLogServiceInterface $audit,
        CurrentContext $context,
        ModuleRoleAssignmentServiceInterface $roleAssignments,
    ): UserAccessService {
        $overviewBuilder = Mockery::mock(UserPlatformAccessOverviewBuilderInterface::class);
        $overviewBuilder->shouldIgnoreMissing();

        return new UserAccessService($repo, $audit, $context, $roleAssignments, $overviewBuilder);
    }
}
