<?php

namespace App\Repositories\Contracts;

use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PermissionRepositoryInterface {
    public function paginate(int $perPage = 30, string $trashed = 'none'): LengthAwarePaginator; // none|with|only
    public function find(string $id, bool $withTrashed = false): ?Permission;
    public function create(array $data): Permission;
    public function delete(Permission $permission): void;        // soft delete
    public function restore(string $id): bool;                   // returns true on success
    public function forceDelete(Permission $permission): void;   // permanent
}
