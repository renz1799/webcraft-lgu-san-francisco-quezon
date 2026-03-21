<?php

namespace App\Modules\Tasks\Repositories\Contracts;

use App\Modules\Tasks\Models\TaskEvent;
use Illuminate\Support\Collection;

interface TaskEventRepositoryInterface
{
    public function create(array $data): TaskEvent;

    public function getForTask(string $taskId): Collection;
}
