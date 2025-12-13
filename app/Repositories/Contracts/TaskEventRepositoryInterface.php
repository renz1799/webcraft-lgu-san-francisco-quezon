<?php

namespace App\Repositories\Contracts;

use App\Models\TaskEvent;
use Illuminate\Support\Collection;

interface TaskEventRepositoryInterface
{
    public function create(array $data): TaskEvent;

    public function getForTask(string $taskId): Collection;
}
