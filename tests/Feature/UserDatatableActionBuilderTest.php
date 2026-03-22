<?php

namespace Tests\Feature;

use App\Core\Builders\User\UserDatatableActionBuilder;
use App\Core\Models\User;
use App\Core\Support\AdminRouteResolver;
use Mockery;
use Tests\TestCase;

class UserDatatableActionBuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_build_keeps_core_lifecycle_actions_for_active_users(): void
    {
        $user = new User();
        $user->id = 'user-1';
        $user->deleted_at = null;

        $adminRoutes = Mockery::mock(AdminRouteResolver::class);
        $adminRoutes->shouldReceive('isModuleScoped')->once()->andReturn(false);
        $adminRoutes->shouldReceive('route')->once()->with('access.users.edit', $user)->andReturn('/users/user-1/permissions/edit');
        $adminRoutes->shouldReceive('route')->once()->with('access.users.status.update', $user)->andReturn('/users/user-1/status');
        $adminRoutes->shouldReceive('route')->once()->with('access.users.destroy', $user)->andReturn('/users/user-1');

        $result = (new UserDatatableActionBuilder($adminRoutes))->build($user);

        $this->assertSame('/users/user-1/permissions/edit', $result['edit_url']);
        $this->assertSame('/users/user-1/status', $result['status_url']);
        $this->assertSame('/users/user-1', $result['delete_url']);
        $this->assertNull($result['restore_url']);
    }

    public function test_build_hides_global_lifecycle_actions_for_module_scoped_users(): void
    {
        $user = new User();
        $user->id = 'user-1';
        $user->deleted_at = null;

        $adminRoutes = Mockery::mock(AdminRouteResolver::class);
        $adminRoutes->shouldReceive('isModuleScoped')->once()->andReturn(true);
        $adminRoutes->shouldReceive('route')->once()->with('access.users.edit', $user)->andReturn('/gso/users/user-1/permissions/edit');

        $result = (new UserDatatableActionBuilder($adminRoutes))->build($user);

        $this->assertSame('/gso/users/user-1/permissions/edit', $result['edit_url']);
        $this->assertNull($result['status_url']);
        $this->assertNull($result['delete_url']);
        $this->assertNull($result['restore_url']);
    }

    public function test_build_keeps_restore_action_core_only_for_archived_users(): void
    {
        $user = new User();
        $user->id = 'user-1';
        $user->deleted_at = now();

        $adminRoutes = Mockery::mock(AdminRouteResolver::class);
        $adminRoutes->shouldReceive('isModuleScoped')->once()->andReturn(false);
        $adminRoutes->shouldReceive('route')->once()->with('access.users.restore', $user)->andReturn('/users/user-1/restore');

        $result = (new UserDatatableActionBuilder($adminRoutes))->build($user);

        $this->assertNull($result['edit_url']);
        $this->assertNull($result['status_url']);
        $this->assertNull($result['delete_url']);
        $this->assertSame('/users/user-1/restore', $result['restore_url']);
    }
}
