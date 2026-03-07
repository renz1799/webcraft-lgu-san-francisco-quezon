<?php

namespace App\Repositories\Contracts;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    public function create(array $data): Task;

    public function findOrFail(string $id): Task;

    public function findOrFailWithTrashed(string $id): Task;

    public function save(Task $task): Task;

    public function delete(Task $task): void;

    public function restore(Task $task): bool;

    public function paginateForAssignee(string $userId, int $perPage = 20): LengthAwarePaginator;

    public function getAvailableForRoles(array $roles, int $limit = 20);

    public function datatable(array $filters, int $page = 1, int $size = 15): array;

    public function countsForSidebar(string $userId, array $roles): array;
}
