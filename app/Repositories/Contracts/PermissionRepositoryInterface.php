<?php

namespace App\Repositories\Contracts;

use App\Models\Permission;

interface PermissionRepositoryInterface
{
    public function datatable(string $moduleId, array $filters, int $page = 1, int $size = 15): array;

    public function findByIdWithTrashed(string $moduleId, string $id): ?Permission;

    public function create(string $moduleId, array $data): Permission;

    public function update(Permission $permission, array $data): Permission;

    public function delete(Permission $permission): void;

    public function restore(Permission $permission): bool;
}
