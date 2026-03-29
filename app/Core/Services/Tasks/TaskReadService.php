<?php

namespace App\Core\Services\Tasks;

use App\Core\Builders\Tasks\Contracts\TaskAdminStatsBuilderInterface;
use App\Core\Builders\Tasks\Contracts\TaskDatatableRowBuilderInterface;
use App\Core\Builders\Tasks\Contracts\UserTaskReassignOptionBuilderInterface;
use App\Core\Models\Module;
use App\Core\Models\Tasks\Task;
use App\Core\Models\User;
use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Core\Policies\Tasks\TaskPolicy;
use App\Core\Repositories\Tasks\Contracts\TaskEventRepositoryInterface;
use App\Core\Repositories\Tasks\Contracts\TaskRepositoryInterface;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Services\Tasks\Contracts\TaskReadServiceInterface;
use App\Core\Services\Tasks\Contracts\TaskShowActionProviderInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaskReadService implements TaskReadServiceInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
        private readonly TaskEventRepositoryInterface $taskEvents,
        private readonly UserRepositoryInterface $users,
        private readonly ModuleAccessServiceInterface $moduleAccess,
        private readonly TaskPolicy $taskPolicy,
        private readonly TaskDatatableRowBuilderInterface $datatableRowBuilder,
        private readonly TaskAdminStatsBuilderInterface $taskAdminStatsBuilder,
        private readonly TaskShowActionProviderInterface $taskShowActions,
        private readonly UserTaskReassignOptionBuilderInterface $userTaskReassignOptionBuilder,
    ) {}

    public function indexData(?User $actor, array|string|null $ownerModuleIds = null): array
    {
        $ownerModules = $this->ownerModulesForActor($actor);
        $availableOwnerModuleIds = $ownerModules
            ->pluck('id')
            ->map(fn ($moduleId) => (string) $moduleId)
            ->filter()
            ->values()
            ->all();
        $ownerModuleIds = $this->scopedOwnerModuleIds($availableOwnerModuleIds, $ownerModuleIds);
        $ownerModules = $ownerModules
            ->filter(fn (Module $module) => in_array((string) $module->id, $ownerModuleIds, true))
            ->values();
        $isAdministrator = $actor?->hasAnyRole(['Administrator', 'admin']) ?? false;

        $adminTaskStats = null;

        if ($isAdministrator && $ownerModuleIds !== []) {
            $adminTaskStats = $this->taskAdminStatsBuilder->build(
                $this->tasks->adminDashboardStats(6, $ownerModuleIds)
            );
        }

        return [
            'adminTaskStats' => $adminTaskStats,
            'ownerModules' => $ownerModules
                ->map(fn (Module $module): array => [
                    'id' => (string) $module->id,
                    'code' => (string) $module->code,
                    'name' => (string) $module->name,
                ])
                ->values()
                ->all(),
        ];
    }

    public function datatable(User $actor, array $params, array|string|null $ownerModuleIds = null): array
    {
        $availableModuleIds = $this->scopedOwnerModuleIds(
            $this->ownerModuleIdsForActor($actor),
            $ownerModuleIds
        );
        $moduleIds = $this->requestedOwnerModuleIds($params, $availableModuleIds);

        if ($moduleIds === []) {
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
        $filters['actor_roles_by_module'] = $this->actorRolesByModule($actor, $moduleIds);
        $filters['can_view_all'] = $actor->hasAnyRole(['Administrator', 'admin'])
            || $actor->can('view All Tasks');

        $payload = $this->tasks->datatable($filters, $page, $size, $moduleIds);

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

    public function sidebarCounts(User $actor, array|string|null $ownerModuleIds = null): array
    {
        $moduleIds = $this->scopedOwnerModuleIds(
            $this->ownerModuleIdsForActor($actor),
            $ownerModuleIds
        );

        if ($moduleIds === []) {
            return [
                'my' => 0,
                'claimable' => 0,
            ];
        }

        return $this->tasks->countsForSidebar(
            (string) $actor->id,
            $this->actorRolesByModule($actor, $moduleIds),
            $moduleIds
        );
    }

    public function findAccessibleOrFail(User $actor, string $taskId): Task
    {
        return $this->assertActorCanReachTask(
            $actor,
            $this->tasks->findOrFail($taskId)
        );
    }

    public function findAccessibleWithTrashedOrFail(User $actor, string $taskId): Task
    {
        return $this->assertActorCanReachTask(
            $actor,
            $this->tasks->findOrFailWithTrashed($taskId)
        );
    }

    private function reassignOptions(User $actor, Task $task): array
    {
        if (! $this->taskPolicy->reassign($actor, $task)) {
            return [];
        }

        $moduleId = trim((string) ($task->module_id ?? ''));
        if (! $moduleId) {
            return [];
        }

        return $this->users->getActiveUsersForModule($moduleId)
            ->map(fn (User $user) => $this->userTaskReassignOptionBuilder->build($user))
            ->values()
            ->all();
    }

    private function ownerModulesForActor(?User $actor): Collection
    {
        if (! $actor) {
            return collect();
        }

        $sharedCapabilityCodes = collect((array) config('modules.shared_capability_codes', []))
            ->map(fn ($code) => strtoupper(trim((string) $code)))
            ->filter()
            ->values()
            ->all();

        return $this->moduleAccess->accessibleModulesForUser($actor)
            ->reject(function (Module $module) use ($sharedCapabilityCodes): bool {
                return in_array(strtoupper((string) $module->code), $sharedCapabilityCodes, true);
            })
            ->values();
    }

    private function ownerModuleIdsForActor(User $actor): array
    {
        return $this->ownerModulesForActor($actor)
            ->pluck('id')
            ->map(fn ($moduleId) => (string) $moduleId)
            ->filter()
            ->values()
            ->all();
    }

    private function scopedOwnerModuleIds(array $availableModuleIds, array|string|null $requestedModuleIds): array
    {
        if ($requestedModuleIds === null) {
            return $availableModuleIds;
        }

        $requestedIds = collect(is_array($requestedModuleIds) ? $requestedModuleIds : [$requestedModuleIds])
            ->map(fn ($moduleId) => trim((string) $moduleId))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($requestedIds === []) {
            return [];
        }

        return array_values(array_intersect($availableModuleIds, $requestedIds));
    }

    private function requestedOwnerModuleIds(array $params, array $availableModuleIds): array
    {
        $requestedModuleId = trim((string) ($params['module_id'] ?? ''));

        if ($requestedModuleId === '') {
            return $availableModuleIds;
        }

        return in_array($requestedModuleId, $availableModuleIds, true)
            ? [$requestedModuleId]
            : [];
    }

    private function actorRolesByModule(User $actor, array $moduleIds): array
    {
        $rolesByModule = [];

        foreach ($moduleIds as $moduleId) {
            $rolesByModule[$moduleId] = $this->users->getRoleNamesInModule($actor, $moduleId);
        }

        return $rolesByModule;
    }

    private function assertActorCanReachTask(User $actor, Task $task): Task
    {
        $moduleId = trim((string) ($task->module_id ?? ''));

        if ($moduleId !== '' && in_array($moduleId, $this->ownerModuleIdsForActor($actor), true)) {
            return $task;
        }

        $exception = new ModelNotFoundException();
        $exception->setModel(Task::class);

        throw $exception;
    }
}
