<?php

namespace App\Services;

use App\Models\Permission;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Services\Contracts\PermissionServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PermissionService implements PermissionServiceInterface
{
    public function __construct(
        private readonly PermissionRepositoryInterface $repo
    ) {}

    public function paginate(int $perPage = 30): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage);
    }

    public function create(array $data): Permission
    {
        // enforce guard default + trim
        $data['guard_name'] = $data['guard_name'] ?? 'web';
        $data['name']       = trim($data['name']);
        $data['page']       = trim($data['page']);

        return $this->repo->create($data);
    }

    public function delete(Permission $permission): void
    {
        $this->repo->delete($permission);
    }
}
