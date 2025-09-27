<?php

namespace App\Repositories\Contracts;

use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PermissionRepositoryInterface
{
    public function paginate(int $perPage = 30): LengthAwarePaginator;
    public function create(array $attributes): Permission;
    public function delete(Permission $permission): void;
}
