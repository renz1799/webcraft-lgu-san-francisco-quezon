<?php

namespace App\Repositories\Eloquent;

use App\Models\Permission;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPermissionRepository implements PermissionRepositoryInterface
{
    public function paginate(int $perPage = 30, string $trashed = 'none'): LengthAwarePaginator
    {
        $q = Permission::query();
        if ($trashed === 'with')  $q->withTrashed();
        if ($trashed === 'only')  $q->onlyTrashed();
        return $q->orderBy('page')->orderBy('name')->paginate($perPage);
    }

    public function find(string $id, bool $withTrashed = false): ?Permission
    {
        $q = $withTrashed ? Permission::withTrashed() : Permission::query();
        return $q->find($id);
    }

    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    public function delete(Permission $permission): void
    {
        $permission->delete(); // soft
    }

    public function restore(string $id): bool
    {
        return (bool) Permission::onlyTrashed()->whereKey($id)->restore();
    }

    public function forceDelete(Permission $permission): void
    {
        $permission->forceDelete();
    }
}
