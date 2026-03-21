<?php

namespace App\Core\Repositories\Contracts;

use App\Core\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function create(array $data): User;
    public function update(User $user, array $data): User;

    public function findById(string $id): ?User;
    public function findByIdWithTrashed(string $id): ?User;
    public function findByEmail(string $email): ?User;

    public function delete(User $user): void;

    public function restore(User $user): bool;

    public function restoreById(string $id): bool;

    public function paginate(int $perPage = 30): LengthAwarePaginator;

    public function datatable(array $filters, int $page = 1, int $size = 15): array;

    public function findInModule(string $id, string $moduleId): ?User;

    public function findActiveInModule(string $id, string $moduleId): ?User;

    public function getActiveUsersForModule(string $moduleId): Collection;

    public function getUserIdsByRoles(array $roleNames): array;
}
