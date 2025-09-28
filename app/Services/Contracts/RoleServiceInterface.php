<?php

namespace App\Services\Contracts;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RoleServiceInterface
{
    /** For the “roles” screen (roles + all permissions). */
    public function indexData(): array;

    /** Optional paginator if you later paginate roles. */
    public function paginateWithPermissions(int $perPage = 30): LengthAwarePaginator;

    public function create(array $data): Role;

    public function update(Role $role, array $data): Role;

    /** Soft delete. */
    public function delete(Role $role): void;
}
