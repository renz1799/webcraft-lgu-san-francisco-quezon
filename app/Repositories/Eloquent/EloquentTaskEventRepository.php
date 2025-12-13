<?php

namespace App\Repositories\Eloquent;

use App\Models\TaskEvent;
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentTaskEventRepository implements TaskEventRepositoryInterface
{
    public function create(array $data): TaskEvent
    {
        return TaskEvent::create($data);
    }

    public function getForTask(string $taskId): Collection
    {
        return TaskEvent::query()
            ->where('task_id', $taskId)
            ->orderBy('created_at')
            ->get();
    }
}
