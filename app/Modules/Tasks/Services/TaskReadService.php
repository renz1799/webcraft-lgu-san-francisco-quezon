<?php

namespace App\Modules\Tasks\Services;

use App\Modules\Tasks\Builders\Contracts\TaskAdminStatsBuilderInterface;
use App\Modules\Tasks\Builders\Contracts\TaskDatatableRowBuilderInterface;
use App\Core\Builders\Contracts\User\UserTaskReassignOptionBuilderInterface;
use App\Modules\Tasks\Models\Task;
use App\Core\Models\User;
use App\Modules\Tasks\Policies\TaskPolicy;
use App\Modules\Tasks\Repositories\Contracts\TaskEventRepositoryInterface;
use App\Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Modules\Tasks\Services\Contracts\TaskReadServiceInterface;
use App\Modules\Tasks\Services\Contracts\TaskShowActionProviderInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaskReadService implements TaskReadServiceInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
        private readonly TaskEventRepositoryInterface $taskEvents,
        private readonly UserRepositoryInterface $users,
        private readonly TaskPolicy $taskPolicy,
        private readonly CurrentContext $context,
        private readonly TaskDatatableRowBuilderInterface $datatableRowBuilder,
        private readonly TaskAdminStatsBuilderInterface $taskAdminStatsBuilder,
        private readonly TaskShowActionProviderInterface $taskShowActions,
        private readonly UserTaskReassignOptionBuilderInterface $userTaskReassignOptionBuilder,
    ) {}

    public function indexData(?User $actor): array
    {
        $isAdministrator = $actor?->hasAnyRole(['Administrator', 'admin']) ?? false;
        $moduleId = $this->context->moduleId();

        $adminTaskStats = null;

        if ($isAdministrator && $moduleId) {
            $adminTaskStats = $this->taskAdminStatsBuilder->build(
                $this->tasks->adminDashboardStats(6, $moduleId)
            );
        }

        return [
            'adminTaskStats' => $adminTaskStats,
        ];
    }

    public function datatable(User $actor, array $params): array
    {
        $moduleId = $this->context->moduleId();

        if (! $moduleId) {
            return [
                'data' => [],
                'last_page' => 1,
                'total' => 0,
            ];
        }

        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));

        $filters = $params;
        unset($filters['page'], $filters['size']);

        $filters['actor_user_id'] = (string) $actor->id;
        $filters['actor_roles'] = $actor->getRoleNames()->values()->all();
        $filters['can_view_all'] = $actor->hasAnyRole(['Administrator', 'admin'])
            || $actor->can('view All Tasks');

        $payload = $this->tasks->datatable($filters, $page, $size, $moduleId);

        $rows = collect($payload['data'] ?? [])
            ->map(function (Task $task) use ($actor) {
                return $this->datatableRowBuilder->build($task, [
                    'can_claim' => $this->taskPolicy->claim($actor, $task),
                    'can_archive' => $this->taskPolicy->delete($actor, $task),
                    'can_restore' => $this->taskPolicy->restore($actor, $task),
                ]);
            })
            ->values()
            ->all();

        return [
            'data' => $rows,
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ];
    }

    public function showData(User $actor, Task $task): array
    {
        return [
            'task' => $task,
            'events' => $this->taskEvents->getForTask((string) $task->id),
            'subjectUrl' => data_get($task->data, 'subject_url'),
            'assignees' => $this->reassignOptions($actor, $task),
            'headerActions' => $this->taskShowActions->getHeaderActions($actor, $task),
        ];
    }

    public function sidebarCounts(User $actor): array
    {
        $moduleId = $this->context->moduleId();

        if (! $moduleId) {
            return [
                'my' => 0,
                'claimable' => 0,
            ];
        }

        return $this->tasks->countsForSidebar(
            (string) $actor->id,
            $actor->getRoleNames()->values()->all(),
            $moduleId
        );
    }

    public function findOrFail(string $taskId): Task
    {
        return $this->tasks->findOrFail($taskId, $this->requireModuleId());
    }

    public function findOrFailWithTrashed(string $taskId): Task
    {
        return $this->tasks->findOrFailWithTrashed($taskId, $this->requireModuleId());
    }

    private function reassignOptions(User $actor, Task $task): array
    {
        if (! $this->taskPolicy->reassign($actor, $task)) {
            return [];
        }

        $moduleId = $this->context->moduleId();
        if (! $moduleId) {
            return [];
        }

        return $this->users->getActiveUsersForModule($moduleId)
            ->map(fn (User $user) => $this->userTaskReassignOptionBuilder->build($user))
            ->values()
            ->all();
    }

    private function requireModuleId(): string
    {
        $moduleId = (string) ($this->context->moduleId() ?? '');

        if ($moduleId !== '') {
            return $moduleId;
        }

        $exception = new ModelNotFoundException();
        $exception->setModel(Task::class);

        throw $exception;
    }
}
