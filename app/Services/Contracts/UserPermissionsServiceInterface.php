<?php

namespace App\Services\Contracts;

use App\Models\User;

interface UserPermissionsServiceInterface
{
    public function indexData(): array;
    public function getUserPermissions(User $user): array;
    public function updateUserRoleAndPermissions(User $user, ?string $roleName, array $permissionNames): void;
    public function ensureDefaultRole(User $user, string $defaultRole = 'User'): void;
    public function deleteUser(User $user): void;
    public function updateStatus(User $user, bool $isActive): void;
}
