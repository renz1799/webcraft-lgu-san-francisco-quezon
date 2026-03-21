<?php

namespace Tests\Feature;

use App\Core\Data\Auth\RegisterUserData;
use App\Core\Models\User;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Services\Auth\RegisterUserService;
use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Core\Services\Contracts\Access\RoleAssignments\ModuleRoleAssignmentServiceInterface;
use App\Core\Support\CurrentContext;
use Mockery;
use Tests\TestCase;

class RegisterUserServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_register_grants_module_access_before_assigning_module_role(): void
    {
        $users = Mockery::mock(UserRepositoryInterface::class);
        $moduleAccess = Mockery::mock(ModuleAccessServiceInterface::class);
        $roleAssignments = Mockery::mock(ModuleRoleAssignmentServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $actor = Mockery::mock(User::class);
        $actor->shouldReceive('hasRole')
            ->once()
            ->with('Administrator')
            ->andReturn(true);

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-1');

        $context->shouldReceive('defaultDepartmentId')
            ->once()
            ->andReturn('department-1');

        $createdUser = new User([
            'id' => 'user-1',
            'username' => 'new.user',
            'email' => 'new.user@example.com',
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $users->shouldReceive('create')
            ->once()
            ->ordered()
            ->with(Mockery::on(function (array $payload): bool {
                return $payload['primary_department_id'] === 'department-1'
                    && $payload['username'] === 'new.user'
                    && $payload['email'] === 'new.user@example.com'
                    && $payload['is_active'] === true
                    && $payload['must_change_password'] === true
                    && is_string($payload['password'])
                    && $payload['password'] !== 'secret123';
            }))
            ->andReturn($createdUser);

        $moduleAccess->shouldReceive('grantActiveModuleAccess')
            ->once()
            ->ordered()
            ->with($createdUser, 'module-1', 'department-1');

        $roleAssignments->shouldReceive('assign')
            ->once()
            ->ordered()
            ->with($createdUser, 'Staff');

        $service = new RegisterUserService($users, $moduleAccess, $roleAssignments, $context);

        $user = $service->register(
            $actor,
            new RegisterUserData(
                username: 'new.user',
                email: 'new.user@example.com',
                password: 'secret123',
                role: 'Staff',
            )
        );

        $this->assertSame($createdUser, $user);
    }
}
