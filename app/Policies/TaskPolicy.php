<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return true;
    }

    public function comment(User $user, Task $task): bool
    {
        return true;
    }

    public function updateStatus(User $user, Task $task): bool
    {
        if ($user->hasAnyRole(['Administrator', 'admin'])) {
            return true;
        }

        return (string) $task->assigned_to_user_id === (string) $user->id;
    }

    public function claim(User $user, Task $task): bool
    {
        if (! empty($task->assigned_to_user_id)) {
            return false;
        }

        $eligible = (array) data_get($task->data, 'eligible_roles', []);
        if (count($eligible) === 0) {
            return true;
        }

        $roles = $user->getRoleNames()->all();

        return count(array_intersect($roles, $eligible)) > 0;
    }

    public function reassign(User $user, Task $task): bool
    {
        return $user->hasAnyRole(['Administrator', 'admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrator', 'admin']);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasAnyRole(['Administrator', 'admin']);
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->hasAnyRole(['Administrator', 'admin']);
    }
}
