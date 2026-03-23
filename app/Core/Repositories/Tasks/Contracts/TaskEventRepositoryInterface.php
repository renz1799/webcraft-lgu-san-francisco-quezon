<?php

namespace App\Core\Repositories\Tasks\Contracts;

use App\Core\Models\Tasks\TaskEvent;
use Illuminate\Support\Collection;

interface TaskEventRepositoryInterface
{
    public function create(array $data): TaskEvent;

    public function getForTask(string $taskId): Collection;
}
