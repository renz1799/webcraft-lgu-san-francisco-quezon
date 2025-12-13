<?php

namespace App\Repositories\Contracts;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    public function create(array $data): Task;

    public function findOrFail(string $id): Task;

    public function save(Task $task): Task;

    public function paginateForAssignee(string $userId, int $perPage = 20): LengthAwarePaginator;

    public function getAvailableForRoles(array $roles, int $limit = 20);

}
