<?php

namespace App\Services\Auth;

use App\Data\Auth\RegisterUserData;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Services\Contracts\Access\RoleAssignments\ModuleRoleAssignmentServiceInterface;
use App\Services\Contracts\Auth\RegisterUserServiceInterface;
use App\Support\CurrentContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class RegisterUserService implements RegisterUserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly ModuleAccessServiceInterface $moduleAccess,
        private readonly ModuleRoleAssignmentServiceInterface $roleAssignments,
        private readonly CurrentContext $currentContext,
) {}

    public function register(User $actor, RegisterUserData $data): User
    {
        if (! $actor->hasRole('Administrator')) {
            abort(403, 'Only Administrators may create users.');
        }

        if ($data->role === 'Administrator') {
            abort(403, 'You may not assign the Administrator role.');
        }

        return DB::transaction(function () use ($data) {
            $moduleId = $this->requireModuleId();
            $departmentId = $this->currentContext->defaultDepartmentId();

            $user = $this->users->create([
                'primary_department_id' => $departmentId,
                'username' => $data->username,
                'email' => $data->email,
                'password' => Hash::make($data->password),
                'is_active' => true,
                'must_change_password' => true,
            ]);

            $this->moduleAccess->grantActiveModuleAccess($user, $moduleId, $departmentId);
            $this->roleAssignments->assign($user, $data->role);

            return $user;
        });
    }

    private function requireModuleId(): string
    {
        $moduleId = (string) $this->currentContext->moduleId();

        if ($moduleId === '') {
            throw new RuntimeException('Current module context is not available.');
        }

        return $moduleId;
    }
}
