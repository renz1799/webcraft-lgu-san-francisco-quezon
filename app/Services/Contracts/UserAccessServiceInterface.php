<?php

namespace App\Services\Contracts;

use App\Models\User;

interface UserAccessServiceInterface
{
    public function indexData(?string $q = null): array;
    public function paginateForPermissionsTable(?string $q, int $page, int $size);
    public function getUserPermissions(User $user): array;
    public function getEditData(User $user): array;
    public function updateUserRoleAndPermissions(User $user, ?string $roleName, array $permissionNames): void;
    public function syncNestedPermissions(User $user, array $nested, ?string $roleName = null): int;
    public function ensureDefaultRole(User $user, string $defaultRole = 'User'): void;
    public function deleteUser(User $user): void;
    public function restoreUser(string|User $user): bool;
    public function forceDeleteUser(string|User $user): bool;
    public function updateStatus(User $user, bool $isActive): void;
    public function resetPasswordToTemporary(User $user): string;
}
