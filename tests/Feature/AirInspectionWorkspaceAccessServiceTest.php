<?php

namespace Tests\Feature;

use App\Core\Models\User;
use App\Core\Models\Tasks\Task;
use App\Core\Services\Tasks\Contracts\TaskServiceInterface;
use App\Core\Support\AdminContextAuthorizer;
use App\Modules\GSO\Services\Air\AirInspectionWorkspaceAccessService;
use Mockery;
use Tests\TestCase;

class AirInspectionWorkspaceAccessServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_assigned_inspector_with_finalize_permission_can_manage_and_finalize(): void
    {
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('allowsPermission')
            ->withArgs(fn ($user, string $permission): bool => $user instanceof User && $permission === 'air.inspect')
            ->andReturn(true);
        $authorizer->shouldReceive('allowsPermission')
            ->withArgs(fn ($user, string $permission): bool => $user instanceof User && $permission === 'air.finalize_inspection')
            ->andReturn(true);
        $authorizer->shouldReceive('allowsAnyPermission')
            ->once()
            ->andReturn(false);

        $tasks = Mockery::mock(TaskServiceInterface::class);
        $tasks->shouldReceive('findLatestBySubject')
            ->once()
            ->with('air', 'air-1')
            ->andReturn(new Task([
                'assigned_to_user_id' => 'user-1',
            ]));

        $service = new AirInspectionWorkspaceAccessService($authorizer, $tasks);
        $access = $service->resolve($this->makeUser('user-1'), 'air-1');

        $this->assertTrue($access['can_manage']);
        $this->assertTrue($access['can_finalize']);
        $this->assertTrue($access['is_assigned_inspector']);
        $this->assertNull($access['warning']);
    }

    public function test_unassigned_inspector_cannot_manage_or_finalize_even_with_finalize_permission(): void
    {
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('allowsPermission')
            ->withArgs(fn ($user, string $permission): bool => $user instanceof User && $permission === 'air.inspect')
            ->andReturn(true);
        $authorizer->shouldReceive('allowsPermission')
            ->withArgs(fn ($user, string $permission): bool => $user instanceof User && $permission === 'air.finalize_inspection')
            ->andReturn(true);
        $authorizer->shouldReceive('allowsAnyPermission')
            ->once()
            ->andReturn(false);

        $tasks = Mockery::mock(TaskServiceInterface::class);
        $tasks->shouldReceive('findLatestBySubject')
            ->once()
            ->with('air', 'air-1')
            ->andReturn(new Task([
                'assigned_to_user_id' => null,
            ]));

        $service = new AirInspectionWorkspaceAccessService($authorizer, $tasks);
        $access = $service->resolve($this->makeUser('user-1'), 'air-1');

        $this->assertFalse($access['can_manage']);
        $this->assertFalse($access['can_finalize']);
        $this->assertFalse($access['is_assigned_inspector']);
        $this->assertSame(
            'You are not currently assigned to this AIR inspection. The workspace is read-only until the task is assigned to you.',
            $access['warning'],
        );
    }

    public function test_elevated_air_manager_can_save_but_still_needs_finalize_permission_to_finalize(): void
    {
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('allowsPermission')
            ->withArgs(fn ($user, string $permission): bool => $user instanceof User && $permission === 'air.inspect')
            ->andReturn(false);
        $authorizer->shouldReceive('allowsPermission')
            ->withArgs(fn ($user, string $permission): bool => $user instanceof User && $permission === 'air.finalize_inspection')
            ->andReturn(false);
        $authorizer->shouldReceive('allowsAnyPermission')
            ->once()
            ->andReturn(true);

        $tasks = Mockery::mock(TaskServiceInterface::class);
        $tasks->shouldReceive('findLatestBySubject')
            ->once()
            ->with('air', 'air-1')
            ->andReturn(null);

        $service = new AirInspectionWorkspaceAccessService($authorizer, $tasks);
        $access = $service->resolve($this->makeUser('manager-1'), 'air-1');

        $this->assertTrue($access['can_manage']);
        $this->assertFalse($access['can_finalize']);
        $this->assertTrue($access['is_assignment_elevated']);
        $this->assertNull($access['warning']);
    }

    private function makeUser(string $id): User
    {
        $user = new User();
        $user->forceFill([
            'id' => $id,
            'username' => $id,
            'email' => "{$id}@example.com",
        ]);

        return $user;
    }
}
