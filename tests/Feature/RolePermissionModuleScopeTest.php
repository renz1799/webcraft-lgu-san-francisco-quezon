<?php

namespace Tests\Feature;

use App\Builders\Access\PermissionAuditDisplayBuilder;
use App\Builders\Access\RoleAuditDisplayBuilder;
use App\Models\Permission;
use App\Models\Role;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\Access\PermissionService;
use App\Services\Access\RoleService;
use App\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Support\CurrentContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Tests\TestCase;

class RolePermissionModuleScopeTest extends TestCase
{
    protected function tearDown(): void
    {
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
