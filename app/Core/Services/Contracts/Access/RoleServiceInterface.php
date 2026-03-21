<?php

namespace App\Core\Services\Contracts\Access;

use App\Core\Models\Role;

interface RoleServiceInterface
{
    public function indexData(): array;

    public function datatable(array $params): array;

    public function create(array $data): Role;

    public function update(Role $role, array $data): Role;

    public function delete(Role $role): void;

    public function restoreRole(string|Role $role): bool;
}
