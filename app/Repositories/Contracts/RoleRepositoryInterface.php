<?php

namespace App\Repositories\Contracts;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RoleRepositoryInterface
{
    public function allWithPermissions(): Collection;

    public function paginateWithPermissions(int $perPage = 30): LengthAwarePaginator;

    public function create(array $data): Role;

    public function update(Role $role, array $data): Role;

    public function syncPermissions(Role $role, array $permissionIds): void;

    /** Soft delete. */
    public function delete(Role $role): void;
}
