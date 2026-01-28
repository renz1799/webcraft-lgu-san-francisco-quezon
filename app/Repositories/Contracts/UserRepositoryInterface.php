<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function create(array $data): User;
    public function update(User $user, array $data): User;

    public function findById(string $id): ?User;
    public function findByIdWithTrashed(string $id): ?User;
    public function findByEmail(string $email): ?User;

    /** Soft-delete */
    public function delete(User $user): void;

    /** Restore a soft-deleted user */
    public function restore(User $user): bool;

    /** Permanently delete (bypass/after soft delete) */
    public function forceDelete(User $user): bool;

    /** (Optional convenience) by id */
    public function restoreById(string $id): bool;
    public function forceDeleteById(string $id): bool;

    public function paginate(int $perPage = 30): LengthAwarePaginator;

    public function assignRoleAndSyncPermissions(User $user, string $roleName): void;

    public function paginateForPermissionsTable(?string $q, int $page, int $size): LengthAwarePaginator;

    public function listForTaskReassign(): array;
}
