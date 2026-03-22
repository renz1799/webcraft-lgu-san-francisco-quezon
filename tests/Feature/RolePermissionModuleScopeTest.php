<?php

namespace Tests\Feature;

use App\Core\Builders\Access\PermissionAuditDisplayBuilder;
use App\Core\Builders\Access\RoleAuditDisplayBuilder;
use App\Core\Models\Permission;
use App\Core\Models\Role;
use App\Core\Repositories\Contracts\PermissionRepositoryInterface;
use App\Core\Repositories\Contracts\RoleRepositoryInterface;
use App\Core\Services\Access\PermissionService;
use App\Core\Services\Access\RoleService;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RolePermissionModuleScopeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->string('name');
            $table->string('page')->nullable();
            $table->string('guard_name');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Mockery::close();

        parent::tearDown();
    }

    public function test_role_service_datatable_uses_current_module_context(): void
    {
        $repo = Mockery::mock(RoleRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-1');

        $repo->shouldReceive('datatable')
            ->once()
            ->with('module-1', [], 1, 15)
            ->andReturn([
                'data' => [],
                'last_page' => 1,
                'total' => 0,
            ]);

        $service = new RoleService($repo, $audit, $context, new RoleAuditDisplayBuilder());

        $this->assertSame([
            'data' => [],
            'last_page' => 1,
            'total' => 0,
        ], $service->datatable([]));
    }

    public function test_role_service_rejects_cross_module_role_mutation(): void
    {
        $repo = Mockery::mock(RoleRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-1');

        $repo->shouldNotReceive('delete');

        $service = new RoleService($repo, $audit, $context, new RoleAuditDisplayBuilder());
        $role = new Role([
            'id' => 'role-1',
            'module_id' => 'module-2',
            'name' => 'Administrator',
            'guard_name' => 'web',
        ]);

        $this->expectException(ModelNotFoundException::class);

        $service->delete($role);
    }

    public function test_role_service_index_data_groups_permissions_for_current_module(): void
    {
        Permission::query()->create([
            'id' => 'permission-1',
            'module_id' => 'module-1',
            'name' => 'view AIR',
            'page' => 'AIR',
            'guard_name' => 'web',
        ]);
        Permission::query()->create([
            'id' => 'permission-2',
            'module_id' => 'module-1',
            'name' => 'modify AIR',
            'page' => 'AIR',
            'guard_name' => 'web',
        ]);
        Permission::query()->create([
            'id' => 'permission-3',
            'module_id' => 'module-1',
            'name' => 'view Unpaged',
            'page' => null,
            'guard_name' => 'web',
        ]);
        Permission::query()->create([
            'id' => 'permission-4',
            'module_id' => 'module-2',
            'name' => 'view Tasks',
            'page' => 'Tasks',
            'guard_name' => 'web',
        ]);

        $repo = Mockery::mock(RoleRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-1');

        $service = new RoleService($repo, $audit, $context, new RoleAuditDisplayBuilder());

        $result = $service->indexData();

        $this->assertCount(3, $result['permissions']);
        $this->assertSame(['AIR', 'Uncategorized'], $result['permissionsByPage']->keys()->all());
        $this->assertSame(
            ['modify AIR', 'view AIR'],
            $result['permissionsByPage']->get('AIR')->pluck('name')->sort()->values()->all()
        );
        $this->assertSame(
            ['view Unpaged'],
            $result['permissionsByPage']->get('Uncategorized')->pluck('name')->all()
        );
    }

    public function test_permission_service_datatable_uses_current_module_context(): void
    {
        $repo = Mockery::mock(PermissionRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-1');

        $repo->shouldReceive('datatable')
            ->once()
            ->with('module-1', [], 1, 15)
            ->andReturn([
                'data' => [],
                'last_page' => 1,
                'total' => 0,
            ]);

        $service = new PermissionService($repo, $audit, $context, new PermissionAuditDisplayBuilder());

        $this->assertSame([
            'data' => [],
            'last_page' => 1,
            'total' => 0,
        ], $service->datatable([]));
    }

    public function test_permission_service_rejects_cross_module_permission_mutation(): void
    {
        $repo = Mockery::mock(PermissionRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-1');

        $repo->shouldNotReceive('delete');

        $service = new PermissionService($repo, $audit, $context, new PermissionAuditDisplayBuilder());
        $permission = new Permission([
            'id' => 'permission-1',
            'module_id' => 'module-2',
            'name' => 'view Tasks',
            'page' => 'Manage Tasks',
            'guard_name' => 'web',
        ]);

        $this->expectException(ModelNotFoundException::class);

        $service->delete($permission);
    }
}
