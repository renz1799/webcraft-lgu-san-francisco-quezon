<?php

namespace App\Core\Services\Tasks\Contracts;

use App\Core\Models\Tasks\Task;
use App\Core\Models\User;

interface TaskReadServiceInterface
{
    public function indexData(?User $actor): array;

    public function datatable(User $actor, array $params): array;

    public function showData(User $actor, Task $task): array;

    public function sidebarCounts(User $actor): array;

    public function findAccessibleOrFail(User $actor, string $taskId): Task;

    public function findAccessibleWithTrashedOrFail(User $actor, string $taskId): Task;
}
