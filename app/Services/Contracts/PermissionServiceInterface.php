<?php

namespace App\Services\Contracts;

use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PermissionServiceInterface
{
    public function paginate(int $perPage = 30): LengthAwarePaginator;
    public function create(array $data): Permission;
    public function delete(Permission $permission): void;
}
