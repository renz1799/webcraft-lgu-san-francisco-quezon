<?php

namespace Tests\Feature;

use Tests\TestCase;

class PermissionFirstRouteMiddlewareTest extends TestCase
{
    public function test_core_access_routes_use_permission_middleware(): void
    {
        $this->assertContains(
            'permission:users.create',
            app('router')->getRoutes()->getByName('sign-up')->gatherMiddleware()
        );

        $this->assertContains(
            'permission:identity_change_requests.approve',
            app('router')->getRoutes()->getByName('identity-change-requests.approve')->gatherMiddleware()
        );

        $this->assertContains(
            'permission:users.view_access|users.manage_access',
            app('router')->getRoutes()->getByName('access.users.index')->gatherMiddleware()
        );

        $this->assertContains(
            'permission:roles.archive',
            app('router')->getRoutes()->getByName('access.roles.destroy')->gatherMiddleware()
        );

        $this->assertContains(
            'permission:permissions.restore',
            app('router')->getRoutes()->getByName('access.permissions.restore')->gatherMiddleware()
        );
    }

    public function test_core_and_gso_audit_routes_use_permission_middleware(): void
    {
        $this->assertContains(
            'permission:audit_logs.print',
            app('router')->getRoutes()->getByName('audit-logs.print.pdf')->gatherMiddleware()
        );

        $this->assertContains(
            'permission:access.users.view|access.users.manage',
            app('router')->getRoutes()->getByName('gso.access.users.index')->gatherMiddleware()
        );

        $this->assertContains(
            'permission:audit_logs.print',
            app('router')->getRoutes()->getByName('gso.audit-logs.print.pdf')->gatherMiddleware()
        );

        $this->assertContains(
            'permission:audit_logs.restore_data',
            app('router')->getRoutes()->getByName('gso.audit.restore')->gatherMiddleware()
        );
    }
}
