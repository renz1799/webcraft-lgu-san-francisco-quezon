<?php

namespace App\Services\Contracts\Access;

use App\Models\Role;

interface RoleServiceInterface
{
    public function indexData(): array;

    public function datatable(array $params): array;

    public function create(array $data): Role;

    public function update(Role $role, array $data): Role;

    public function delete(Role $role): void;

    public function restoreRole(string|Role $role): bool;
}
