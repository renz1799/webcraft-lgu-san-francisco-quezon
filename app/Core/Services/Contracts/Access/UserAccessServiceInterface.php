<?php

namespace App\Core\Services\Contracts\Access;

use App\Core\Models\User;

interface UserAccessServiceInterface
{
    public function datatable(array $params): array;
    public function getUserPermissions(User $user): array;
    public function getEditData(User $user): array;
    public function updateUserRoleAndPermissions(User $user, ?string $roleName, array $permissionNames): void;
    public function syncNestedPermissions(User $user, array $nested, ?string $roleName = null): int;
    public function ensureDefaultRole(User $user, string $defaultRole = 'User'): void;
    public function deleteUser(User $user): void;
    public function restoreUser(string|User $user): bool;
    public function updateStatus(User $user, bool $isActive): void;
    public function updateModuleStatus(User $user, bool $isActive): void;
    public function resetPasswordToTemporary(User $user): string;
}
