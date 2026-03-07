<?php

namespace App\Repositories\Contracts;

use App\Models\Permission;

interface PermissionRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array;

    public function findByIdWithTrashed(string $id): ?Permission;

    public function create(array $data): Permission;

    public function update(Permission $permission, array $data): Permission;

    public function delete(Permission $permission): void;

    public function restore(Permission $permission): bool;
}
