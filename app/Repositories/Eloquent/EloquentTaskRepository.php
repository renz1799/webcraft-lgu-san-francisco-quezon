<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function findOrFail(string $id): Task
    {
        return Task::query()->findOrFail($id);
    }

    public function save(Task $task): Task
    {
        $task->save();
        return $task;
    }

    public function paginateForAssignee(string $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Task::query()
            ->where('assigned_to_user_id', $userId)
            ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'done', 'cancelled')")
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
    
    public function getAvailableForRoles(array $roles, int $limit = 20)
    {
        // MySQL JSON contains; this will work if `data` is JSON and eligible_roles is array.
        // NOTE: If you use SQLite locally, JSON query differs.
        return Task::query()
            ->whereNull('assigned_to_user_id')
            ->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS])
            ->where(function ($q) use ($roles) {
                foreach ($roles as $role) {
                    $q->orWhereJsonContains('data->eligible_roles', $role);
                }
            })
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

}
