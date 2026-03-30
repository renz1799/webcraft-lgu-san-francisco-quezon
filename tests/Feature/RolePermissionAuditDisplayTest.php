<?php

namespace Tests\Feature;

use App\Core\Builders\Access\PermissionAuditDisplayBuilder;
use App\Core\Builders\Access\RoleAuditDisplayBuilder;
use Mockery;
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
        $builder = new RoleAuditDisplayBuilder();

        $display = $builder->buildUpdatedDisplay(
            ['name' => 'Staff', 'permissions' => ['tasks.view']],
            ['name' => 'Staff', 'permissions' => ['tasks.view', 'tasks.update', 'tasks.archive']]
        );

        $this->assertSame('Role updated: Staff', $display['summary']);
        $this->assertSame('Added Permissions', $display['sections'][0]['items'][1]['label']);
        $this->assertSame(['Tasks / Update', 'Tasks / Archive'], $display['sections'][0]['items'][1]['value']);
        $this->assertSame([], $display['sections'][0]['items'][2]['value']);
        $this->assertSame(3, $display['request_details']['Permission Count']);
    }

    public function test_permission_updated_display_uses_human_labels(): void
    {
        $builder = new PermissionAuditDisplayBuilder();

        $display = $builder->buildUpdatedDisplay(
            ['name' => 'tasks.update', 'page' => 'tasks', 'guard_name' => 'web'],
            ['name' => 'tasks.archive', 'page' => 'task_management', 'guard_name' => 'web']
        );

        $this->assertSame('Permission updated: Tasks / Archive', $display['summary']);
        $this->assertSame('Permission Name', $display['sections'][0]['items'][0]['label']);
        $this->assertSame('Tasks / Update', $display['sections'][0]['items'][0]['before']);
        $this->assertSame('Tasks / Archive', $display['sections'][0]['items'][0]['after']);
        $this->assertSame('Page', $display['sections'][0]['items'][1]['label']);
        $this->assertSame('Tasks', $display['sections'][0]['items'][1]['before']);
        $this->assertSame('Task Management', $display['sections'][0]['items'][1]['after']);
    }
}
