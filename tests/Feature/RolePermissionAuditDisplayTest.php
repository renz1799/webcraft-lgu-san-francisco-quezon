<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\Access\PermissionService;
use App\Services\Access\RoleService;
use App\Services\Contracts\AuditLogServiceInterface;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class RolePermissionAuditDisplayTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_role_updated_display_shows_permission_differences(): void
    {
        $repo = Mockery::mock(RoleRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);

        $service = new RoleService($repo, $audit);
        $method = new ReflectionMethod($service, 'buildRoleUpdatedDisplay');
        $method->setAccessible(true);

        $display = $method->invoke(
            $service,
            ['name' => 'Staff', 'permissions' => ['view Tasks']],
            ['name' => 'Staff', 'permissions' => ['view Tasks', 'modify Tasks', 'delete Tasks']]
        );

        $this->assertSame('Role updated: Staff', $display['summary']);
        $this->assertSame('Added Permissions', $display['sections'][0]['items'][1]['label']);
        $this->assertSame(['Modify Tasks', 'Delete Tasks'], $display['sections'][0]['items'][1]['value']);
        $this->assertSame([], $display['sections'][0]['items'][2]['value']);
        $this->assertSame(3, $display['request_details']['Permission Count']);
    }

    public function test_permission_updated_display_uses_human_labels(): void
    {
        $repo = Mockery::mock(PermissionRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);

        $service = new PermissionService($repo, $audit);
        $method = new ReflectionMethod($service, 'buildPermissionUpdatedDisplay');
        $method->setAccessible(true);

        $display = $method->invoke(
            $service,
            ['name' => 'modify tasks', 'page' => 'tasks', 'guard_name' => 'web'],
            ['name' => 'delete tasks', 'page' => 'task_management', 'guard_name' => 'web']
        );

        $this->assertSame('Permission updated: Delete Tasks', $display['summary']);
        $this->assertSame('Permission Name', $display['sections'][0]['items'][0]['label']);
        $this->assertSame('Modify Tasks', $display['sections'][0]['items'][0]['before']);
        $this->assertSame('Delete Tasks', $display['sections'][0]['items'][0]['after']);
        $this->assertSame('Page', $display['sections'][0]['items'][1]['label']);
        $this->assertSame('Tasks', $display['sections'][0]['items'][1]['before']);
        $this->assertSame('Task Management', $display['sections'][0]['items'][1]['after']);
    }
}
