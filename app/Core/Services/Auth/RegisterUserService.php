<?php

namespace App\Core\Services\Auth;

use App\Core\Data\Auth\RegisterUserData;
use App\Core\Models\User;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Core\Services\Contracts\Access\ModuleDepartmentResolverInterface;
use App\Core\Services\Contracts\Access\RoleAssignments\ModuleRoleAssignmentServiceInterface;
use App\Core\Services\Contracts\Auth\RegisterUserServiceInterface;
use App\Core\Support\AdminContextAuthorizer;
use App\Core\Support\CurrentContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class RegisterUserService implements RegisterUserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly ModuleAccessServiceInterface $moduleAccess,
        private readonly ModuleDepartmentResolverInterface $moduleDepartments,
        private readonly ModuleRoleAssignmentServiceInterface $roleAssignments,
        private readonly AdminContextAuthorizer $authorizer,
        private readonly CurrentContext $currentContext,
) {}

    public function register(User $actor, RegisterUserData $data): User
    {
        if (! $this->authorizer->canRegisterUsers($actor)) {
            abort(403, 'Only Administrators may create users.');
        }

        if ($data->role === 'Administrator') {
            abort(403, 'You may not assign the Administrator role.');
        }

        return DB::transaction(function () use ($data) {
            $moduleId = $this->requireModuleId();
            $baseDepartmentId = $this->currentContext->defaultDepartmentId();
            $moduleDepartmentId = $this->moduleDepartments->resolveForModule($moduleId);

            $user = $this->users->create([
                'primary_department_id' => $baseDepartmentId,
                'username' => $data->username,
                'email' => $data->email,
                'password' => Hash::make($data->password),
                'is_active' => true,
                'must_change_password' => true,
            ]);

            $this->moduleAccess->grantActiveModuleAccess($user, $moduleId, $moduleDepartmentId);
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
