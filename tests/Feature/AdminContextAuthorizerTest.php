<?php

namespace Tests\Feature;

use App\Core\Models\Module;
use App\Core\Models\User;
use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Core\Services\Contracts\Access\RoleAssignments\ModuleRoleAssignmentServiceInterface;
use App\Core\Support\AdminContextAuthorizer;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class AdminContextAuthorizerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createPermissionSchema();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_platform_context_manage_access_is_permission_first(): void
    {
        DB::table('permissions')->insert([
            'id' => 'permission-manage-access',
            'module_id' => 'module-core',
            'name' => 'users.manage_access',
            'page' => 'Core / Users',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('model_has_permissions')->insert([
            'permission_id' => 'permission-manage-access',
            'module_id' => 'module-core',
            'model_type' => User::class,
            'model_id' => 'user-1',
        ]);

        $user = new User();
        $user->forceFill(['id' => 'user-1']);

        $platformModule = new Module();
        $platformModule->forceFill([
            'id' => 'module-core',
            'code' => 'CORE',
            'type' => Module::TYPE_PLATFORM,
        ]);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-core');
        $context->shouldReceive('module')
            ->once()
            ->andReturn($platformModule);

        $moduleAccess = Mockery::mock(ModuleAccessServiceInterface::class);
        $moduleAccess->shouldReceive('hasActiveModuleAccess')
            ->once()
            ->with($user, 'module-core')
            ->andReturn(true);

        $moduleRoles = Mockery::mock(ModuleRoleAssignmentServiceInterface::class);
        $moduleRoles->shouldNotReceive('hasRole');

        $authorizer = new AdminContextAuthorizer($context, $moduleAccess, $moduleRoles);

        $this->assertTrue($authorizer->canManageCurrentContextAccess($user));
    }

    public function test_permission_lookup_requires_normalized_permissions_inside_current_module(): void
    {
        DB::table('permissions')->insert([
            [
                'id' => 'permission-1',
                'module_id' => 'module-1',
                'name' => 'audit_logs.view',
                'page' => 'Audit Logs',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'permission-2',
                'module_id' => 'module-2',
                'name' => 'login_logs.view',
                'page' => 'Login Logs',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('model_has_permissions')->insert([
            [
                'permission_id' => 'permission-1',
                'module_id' => 'module-1',
                'model_type' => User::class,
                'model_id' => 'user-1',
            ],
            [
                'permission_id' => 'permission-2',
                'module_id' => 'module-2',
                'model_type' => User::class,
                'model_id' => 'user-1',
            ],
        ]);

        $user = new User();
        $user->forceFill(['id' => 'user-1']);

        $module = new Module();
        $module->forceFill([
            'id' => 'module-1',
            'code' => 'GSO',
            'type' => Module::TYPE_BUSINESS,
        ]);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')
            ->times(3)
            ->andReturn('module-1');
        $context->shouldReceive('module')
            ->never();

        $moduleAccess = Mockery::mock(ModuleAccessServiceInterface::class);
        $moduleAccess->shouldReceive('hasActiveModuleAccess')
            ->times(3)
            ->with($user, 'module-1')
            ->andReturn(true);

        $moduleRoles = Mockery::mock(ModuleRoleAssignmentServiceInterface::class);
        $moduleRoles->shouldIgnoreMissing();

        $authorizer = new AdminContextAuthorizer($context, $moduleAccess, $moduleRoles);

        $this->assertTrue($authorizer->allowsPermission($user, 'audit_logs.view'));
        $this->assertTrue($authorizer->hasAnyPermission($user, 'audit_logs.view'));
        $this->assertFalse($authorizer->allowsPermission($user, 'login_logs.view'));
    }

    public function test_permission_lookup_requires_normalized_gso_feature_permissions_inside_current_module(): void
    {
        DB::table('permissions')->insert([
            'id' => 'permission-asset-types-manage',
            'module_id' => 'module-1',
            'name' => 'asset_types.update',
            'page' => 'GSO / Reference Data',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('model_has_permissions')->insert([
            'permission_id' => 'permission-asset-types-manage',
            'module_id' => 'module-1',
            'model_type' => User::class,
            'model_id' => 'user-1',
        ]);

        $user = new User();
        $user->forceFill(['id' => 'user-1']);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')
            ->times(3)
            ->andReturn('module-1');
        $context->shouldReceive('module')
            ->never();

        $moduleAccess = Mockery::mock(ModuleAccessServiceInterface::class);
        $moduleAccess->shouldReceive('hasActiveModuleAccess')
            ->times(3)
            ->with($user, 'module-1')
            ->andReturn(true);

        $moduleRoles = Mockery::mock(ModuleRoleAssignmentServiceInterface::class);
        $moduleRoles->shouldIgnoreMissing();

        $authorizer = new AdminContextAuthorizer($context, $moduleAccess, $moduleRoles);

        $this->assertTrue($authorizer->allowsPermission($user, 'asset_types.update'));
        $this->assertTrue($authorizer->allowsAnyPermission($user, ['asset_types.create', 'asset_types.update']));
        $this->assertFalse($authorizer->allowsPermission($user, 'asset_types.archive'));
    }

    private function createPermissionSchema(): void
    {
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
            $table->uuid('permission_id');
            $table->uuid('module_id');
            $table->string('model_type');
            $table->uuid('model_id');
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->uuid('role_id');
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->uuid('role_id');
            $table->uuid('module_id');
            $table->string('model_type');
            $table->uuid('model_id');
        });
    }
}
