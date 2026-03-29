<?php

namespace App\Core\Repositories\Contracts;

use App\Core\Models\UserIdentityChangeRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserIdentityChangeRequestRepositoryInterface
{
    public function create(array $attributes): UserIdentityChangeRequest;

    public function save(UserIdentityChangeRequest $request): UserIdentityChangeRequest;

    public function findById(string $id): ?UserIdentityChangeRequest;

    public function findPendingForUser(string $userId): ?UserIdentityChangeRequest;

    public function findLatestForUser(string $userId): ?UserIdentityChangeRequest;

    public function paginateForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
