<?php

namespace App\Repositories\Contracts;

use App\Models\Role;

interface RoleRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array;

    public function create(array $data): Role;

    public function update(Role $role, array $data): Role;

    public function syncPermissions(Role $role, array $permissionIds): void;

    public function delete(Role $role): void;

    public function findByIdWithTrashed(string $id): ?Role;

    public function restore(Role $role): bool;
}
