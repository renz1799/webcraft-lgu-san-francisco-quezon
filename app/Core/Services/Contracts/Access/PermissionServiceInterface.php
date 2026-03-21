<?php

namespace App\Core\Services\Contracts\Access;

use App\Core\Models\Permission;

interface PermissionServiceInterface
{
    public function datatable(array $params): array;

    public function create(array $data): Permission;

    public function update(Permission $permission, array $data): Permission;

    public function delete(Permission $permission): void;

    public function restorePermission(string|Permission $permission): bool;
}
