<?php

namespace App\Core\Services\Contracts\Access\RoleAssignments;

use App\Core\Models\Role;
use App\Core\Models\User;
use Illuminate\Support\Collection;

interface ModuleRoleAssignmentServiceInterface
{
    public function assign(User $user, string|Role $role): void;

    public function assignInModule(User $user, string|Role $role, string $moduleId): void;

    public function sync(User $user, array $roles): void;

    public function syncInModule(User $user, array $roles, string $moduleId): void;

    public function revoke(User $user, string|Role $role): void;

    public function revokeAll(User $user): void;

    public function roles(User $user): Collection;

    public function rolesInModule(User $user, string $moduleId): Collection;

    public function hasRole(User $user, string $roleName): bool;

    public function hasRoleInModule(User $user, string $roleName, string $moduleId): bool;
}
