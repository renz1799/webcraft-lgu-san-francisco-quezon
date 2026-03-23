<?php

namespace App\Core\Policies\Tasks;

use App\Core\Models\Tasks\Task;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Models\User;
use App\Core\Support\CurrentContext;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        if (! $this->userHasActiveModuleAccess($user, (string) $task->module_id)) {
            return false;
        }

        if ($this->canViewAll($user)) {
            return true;
        }

        if ((string) $task->created_by_user_id === (string) $user->id) {
            return true;
        }

        if ((string) $task->assigned_to_user_id === (string) $user->id) {
            return true;
        }

        return $this->claimableBy($user, $task);
    }

    public function comment(User $user, Task $task): bool
    {
        if (! $this->userHasActiveModuleAccess($user, (string) $task->module_id)) {
            return false;
        }

        if ($this->isAdministrator($user)) {
            return true;
        }

        return (string) $task->assigned_to_user_id === (string) $user->id
            || (string) $task->created_by_user_id === (string) $user->id;
    }

    public function updateStatus(User $user, Task $task): bool
    {
        if (! $this->userHasActiveModuleAccess($user, (string) $task->module_id)) {
            return false;
        }

        if ($this->isAdministrator($user)) {
            return true;
        }

        return (string) $task->assigned_to_user_id === (string) $user->id;
    }

    public function claim(User $user, Task $task): bool
    {
        if (! $this->userHasActiveModuleAccess($user, (string) $task->module_id)) {
            return false;
        }

        return $this->claimableBy($user, $task);
    }

    public function reassign(User $user, Task $task): bool
    {
        if (! $this->userHasActiveModuleAccess($user, (string) $task->module_id)) {
            return false;
        }

        return $this->isAdministrator($user) || $user->can('modify Reassign Tasks');
    }

    public function create(User $user): bool
    {
        return $this->isAdministrator($user) && $this->hasCurrentModuleAccess($user);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->isAdministrator($user)
            && $this->userHasActiveModuleAccess($user, (string) $task->module_id);
    }

    public function restore(User $user, Task $task): bool
    {
        return $this->isAdministrator($user)
            && $this->userHasActiveModuleAccess($user, (string) $task->module_id);
    }

    private function isAdministrator(User $user): bool
    {
        return $user->hasAnyRole(['Administrator', 'admin']);
    }

    private function canViewAll(User $user): bool
    {
        return $this->isAdministrator($user) || $user->can('view All Tasks');
    }

    private function claimableBy(User $user, Task $task): bool
    {
        if (! empty($task->assigned_to_user_id)) {
            return false;
        }

        if (! in_array((string) $task->status, [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS], true)) {
            return false;
        }

        $eligible = (array) data_get($task->data, 'eligible_roles', []);
        if ($eligible === []) {
            return true;
        }

        $roles = app(UserRepositoryInterface::class)->getRoleNamesInModule($user, (string) $task->module_id);

        return count(array_intersect($roles, $eligible)) > 0;
    }

    private function hasCurrentModuleAccess(User $user): bool
    {
        $moduleId = app(CurrentContext::class)->moduleId();

        return $moduleId !== null && $this->userHasActiveModuleAccess($user, $moduleId);
    }

    private function userHasActiveModuleAccess(User $user, string $moduleId): bool
    {
        if ($moduleId === '') {
            return false;
        }

        return $user->userModules()
            ->where('module_id', $moduleId)
            ->where('is_active', true)
            ->exists();
    }
}
