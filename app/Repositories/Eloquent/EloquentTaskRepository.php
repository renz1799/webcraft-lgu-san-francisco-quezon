<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

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

public function paginateForTable(
    string $userId,
    array $roles,
    array $filters,
    int $page = 1,
    int $size = 15
): LengthAwarePaginator {
    $q      = trim((string) ($filters['q'] ?? ''));
    $scope  = trim((string) ($filters['scope'] ?? 'mine')); // mine | available | all
    $status = trim((string) ($filters['status'] ?? ''));

    $builder = Task::query();

    if ($scope === 'all') {
        // All tasks — controller must guard admin-only access
    }
    elseif ($scope === 'available') {
        $builder
            ->whereNull('assigned_to_user_id')
            ->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS])
            ->where(function ($qb) use ($roles) {

                // If no eligible_roles defined → claimable by anyone
                $qb->whereNull('data->eligible_roles');

                foreach ($roles as $role) {
                    $qb->orWhereJsonContains('data->eligible_roles', $role);
                }
            });
    }
    else {
        // default: mine
        $builder->where('assigned_to_user_id', $userId);
    }

    if ($status !== '') {
        $builder->where('status', $status);
    }

    if ($q !== '') {
        $builder->where(function ($qb) use ($q) {
            $qb->where('title', 'like', "%{$q}%");
        });
    }

    $builder->orderByRaw("
        CASE status
            WHEN 'pending' THEN 1
            WHEN 'in_progress' THEN 2
            WHEN 'done' THEN 3
            WHEN 'cancelled' THEN 4
            ELSE 99
        END
    ")->orderByDesc('created_at');

    return $builder->paginate($size, ['*'], 'page', $page);
}

public function countsForSidebar(string $userId, array $roles): array
{
    // My Tasks count: assigned to me AND NOT done
    $my = Task::query()
        ->where('assigned_to_user_id', $userId)
          ->whereNotIn('status', [Task::STATUS_DONE, Task::STATUS_CANCELLED])
        ->count();

    // Claimable count: unassigned + pending/in_progress + eligible_roles match (or missing)
    $claimable = Task::query()
        ->whereNull('assigned_to_user_id')
        ->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS])
        ->where(function ($qb) use ($roles) {

            // If no eligible_roles defined → claimable by anyone (your rule)
            $qb->whereNull('data->eligible_roles');

            foreach ($roles as $role) {
                $qb->orWhereJsonContains('data->eligible_roles', $role);
            }
        })
        ->count();

    return [
        'my' => $my,
        'claimable' => $claimable,
    ];
}


}
