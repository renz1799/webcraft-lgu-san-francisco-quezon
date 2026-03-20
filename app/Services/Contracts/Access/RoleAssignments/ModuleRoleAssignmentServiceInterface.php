<?php

namespace App\Services\Contracts\Access\RoleAssignments;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

interface ModuleRoleAssignmentServiceInterface
{
    public function assign(User $user, string|Role $role): void;

    public function sync(User $user, array $roles): void;

    public function revoke(User $user, string|Role $role): void;

    public function revokeAll(User $user): void;

    public function roles(User $user): Collection;

    public function hasRole(User $user, string $roleName): bool;
}