<?php

namespace App\Services\Contracts\Tasks;

use App\Models\Task;
use App\Models\User;

interface TaskReadServiceInterface
{
    public function indexData(?User $actor): array;

    public function datatable(User $actor, array $params): array;

    public function showData(User $actor, Task $task): array;

    public function sidebarCounts(User $actor): array;

    public function findOrFail(string $taskId): Task;

    public function findOrFailWithTrashed(string $taskId): Task;
}
