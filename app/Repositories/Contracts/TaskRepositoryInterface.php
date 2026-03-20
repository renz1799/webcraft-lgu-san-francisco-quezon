<?php

namespace App\Repositories\Contracts;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    public function create(array $data): Task;

    public function findLatestBySubject(string $subjectType, string $subjectId, ?string $moduleId = null): ?Task;

    public function findOrFail(string $id, ?string $moduleId = null): Task;

    public function findOrFailWithTrashed(string $id, ?string $moduleId = null): Task;

    public function save(Task $task): Task;

    public function delete(Task $task): void;

    public function restore(Task $task): bool;

    public function paginateForAssignee(string $userId, int $perPage = 20, ?string $moduleId = null): LengthAwarePaginator;

    public function getAvailableForRoles(array $roles, int $limit = 20, ?string $moduleId = null);

    public function datatable(array $filters, int $page = 1, int $size = 15, ?string $moduleId = null): array;

    public function countsForSidebar(string $userId, array $roles, ?string $moduleId = null): array;

    public function adminDashboardStats(int $months = 6, ?string $moduleId = null): array;
}
