<?php

namespace App\Modules\Tasks\Services\Contracts;

use App\Modules\Tasks\Models\Task;
use App\Core\Models\User;

interface TaskReadServiceInterface
{
    public function indexData(?User $actor): array;

    public function datatable(User $actor, array $params): array;

    public function showData(User $actor, Task $task): array;

    public function sidebarCounts(User $actor): array;

    public function findOrFail(string $taskId): Task;

    public function findOrFailWithTrashed(string $taskId): Task;
}
