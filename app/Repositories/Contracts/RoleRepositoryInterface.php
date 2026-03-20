<?php

namespace App\Repositories\Contracts;

use App\Models\Role;

interface RoleRepositoryInterface
{
    public function datatable(string $moduleId, array $filters, int $page = 1, int $size = 15): array;

    public function create(string $moduleId, array $data): Role;

    public function update(Role $role, array $data): Role;

    public function syncPermissions(Role $role, string $moduleId, array $permissionIds): void;

    public function delete(Role $role): void;

    public function findByIdWithTrashed(string $moduleId, string $id): ?Role;

    public function restore(Role $role): bool;
}
