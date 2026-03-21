<?php

namespace Tests\Feature;

use App\Core\Models\Permission;
use App\Core\Models\Role;
use App\Core\Models\User;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Services\Access\UserAccessService;
use App\Core\Services\Contracts\Access\RoleAssignments\ModuleRoleAssignmentServiceInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Support\CurrentContext;
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
    }
}
