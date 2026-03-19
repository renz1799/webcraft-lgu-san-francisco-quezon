<?php

namespace App\Services\Auth;

use App\Data\Auth\RegisterUserData;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Services\Contracts\Auth\RegisterUserServiceInterface;
use App\Support\CurrentContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUserService implements RegisterUserServiceInterface
{
public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly ModuleAccessServiceInterface $moduleAccess,
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
        $moduleId = (string) $this->currentContext->moduleId();
        $departmentId = $this->currentContext->defaultDepartmentId();

        $user = $this->users->create([
            'primary_department_id' => $departmentId,
            'username' => $data->username,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $this->users->assignRoleAndSyncPermissions($user, $data->role);

        if ($moduleId !== '') {
            $this->moduleAccess->grantActiveModuleAccess($user, $moduleId, $departmentId);
        }

        return $user;
    });
}
}