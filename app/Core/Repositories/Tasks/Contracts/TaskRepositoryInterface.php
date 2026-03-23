<?php

namespace App\Core\Repositories\Tasks\Contracts;

use App\Core\Models\Tasks\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    public function create(array $data): Task;

    public function findLatestBySubject(string $subjectType, string $subjectId, array|string|null $moduleIds = null): ?Task;

    public function findOrFail(string $id, array|string|null $moduleIds = null): Task;

    public function findOrFailWithTrashed(string $id, array|string|null $moduleIds = null): Task;

    public function save(Task $task): Task;

    public function delete(Task $task): void;

    public function restore(Task $task): bool;

    public function paginateForAssignee(string $userId, int $perPage = 20, array|string|null $moduleIds = null): LengthAwarePaginator;

    public function getAvailableForRoles(array $rolesByModule = [], int $limit = 20, array|string|null $moduleIds = null);

    public function datatable(array $filters, int $page = 1, int $size = 15, array|string|null $moduleIds = null): array;

    public function countsForSidebar(string $userId, array $rolesByModule = [], array|string|null $moduleIds = null): array;

    public function adminDashboardStats(int $months = 6, array|string|null $moduleIds = null): array;
}
