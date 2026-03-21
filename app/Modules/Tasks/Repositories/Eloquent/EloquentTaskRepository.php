<?php

namespace App\Modules\Tasks\Repositories\Eloquent;

use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function findLatestBySubject(string $subjectType, string $subjectId, ?string $moduleId = null): ?Task
    {
        return $this->queryInModule($moduleId)
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->orderByDesc('created_at')
            ->first();
    }

    public function findOrFail(string $id, ?string $moduleId = null): Task
    {
        return $this->queryInModule($moduleId)
            ->findOrFail($id);
    }

    public function findOrFailWithTrashed(string $id, ?string $moduleId = null): Task
    {
        return $this->queryInModule($moduleId)
            ->withTrashed()
            ->findOrFail($id);
    }

    public function save(Task $task): Task
    {
        $task->save();

        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    public function restore(Task $task): bool
    {
        return (bool) $task->restore();
    }

    public function paginateForAssignee(string $userId, int $perPage = 20, ?string $moduleId = null): LengthAwarePaginator
    {
        return $this->queryInModule($moduleId)
            ->where('assigned_to_user_id', $userId)
            ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'done', 'cancelled')")
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getAvailableForRoles(array $roles, int $limit = 20, ?string $moduleId = null)
    {
        return $this->applyEligibleRoleScope(
            $this->queryInModule($moduleId)
                ->whereNull('assigned_to_user_id')
                ->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS]),
            $roles
        )
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function datatable(array $filters, int $page = 1, int $size = 15, ?string $moduleId = null): array
    {
        $page = max(1, (int) $page);
        $size = max(1, min((int) $size, 100));

        $query = $this->buildDatatableQuery($filters, $moduleId);

        $total = (clone $query)->count();
        $lastPage = $total > 0 ? (int) ceil($total / $size) : 1;
        $page = min($page, $lastPage);

        $rows = $this->applyDatatableSort($query, $filters)
            ->forPage($page, $size)
            ->get()
            ->values()
            ->all();

        return [
            'data' => $rows,
            'last_page' => $lastPage,
            'total' => (int) $total,
        ];
    }

    public function countsForSidebar(string $userId, array $roles, ?string $moduleId = null): array
    {
        $my = $this->queryInModule($moduleId)
            ->where('assigned_to_user_id', $userId)
            ->whereNotIn('status', [Task::STATUS_DONE, Task::STATUS_CANCELLED])
            ->count();

        $claimable = $this->applyEligibleRoleScope(
            $this->queryInModule($moduleId)
                ->whereNull('assigned_to_user_id')
                ->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS]),
            $roles
        )->count();

        return [
            'my' => $my,
            'claimable' => $claimable,
        ];
    }

    public function adminDashboardStats(int $months = 6, ?string $moduleId = null): array
    {
        $months = max(2, min($months, 12));
        $now = Carbon::now();

        $monthWindows = collect(range($months - 1, 0))
            ->map(function (int $offset) use ($now) {
                $start = $now->copy()->startOfMonth()->subMonths($offset);
                $end = $offset === 0
                    ? $now->copy()
                    : $start->copy()->endOfMonth();

                return [
                    'key' => $start->format('Y-m'),
                    'label' => $start->format('M'),
                    'start' => $start,
                    'end' => $end,
                ];
            })
            ->values()
            ->all();

        $keys = array_column($monthWindows, 'key');
        $newCounts = array_fill_keys($keys, 0);
        $completedCounts = array_fill_keys($keys, 0);
        $pendingSnapshots = array_fill_keys($keys, 0);
        $inProgressSnapshots = array_fill_keys($keys, 0);

        $tasks = $this->queryInModule($moduleId)
            ->select([
                'id',
                'module_id',
                'status',
                'created_at',
                'started_at',
                'completed_at',
                'cancelled_at',
                'deleted_at',
            ])
            ->with([
                'events' => function ($query) {
                    $query->select(['id', 'task_id', 'event_type', 'to_status', 'created_at'])
                        ->where('event_type', 'status_changed')
                        ->whereNotNull('to_status')
                        ->orderBy('created_at');
                },
            ])
            ->where('created_at', '<=', $now)
            ->orderBy('created_at')
            ->get();

        foreach ($tasks as $task) {
            if ($task->created_at) {
                $key = $task->created_at->format('Y-m');
                if (array_key_exists($key, $newCounts)) {
                    $newCounts[$key]++;
                }
            }

            if ($task->completed_at) {
                $key = $task->completed_at->format('Y-m');
                if (array_key_exists($key, $completedCounts)) {
                    $completedCounts[$key]++;
                }
            }
        }

        foreach ($monthWindows as $window) {
            $pendingCount = 0;
            $inProgressCount = 0;

            foreach ($tasks as $task) {
                $statusAtWindowEnd = $this->resolveStatusAtWindowEnd($task, $window['end']);

                if ($statusAtWindowEnd === Task::STATUS_PENDING) {
                    $pendingCount++;
                }

                if ($statusAtWindowEnd === Task::STATUS_IN_PROGRESS) {
                    $inProgressCount++;
                }
            }

            $pendingSnapshots[$window['key']] = $pendingCount;
            $inProgressSnapshots[$window['key']] = $inProgressCount;
        }

        $currentPending = $this->queryInModule($moduleId)
            ->where('status', Task::STATUS_PENDING)
            ->count();

        $currentInProgress = $this->queryInModule($moduleId)
            ->where('status', Task::STATUS_IN_PROGRESS)
            ->count();

        $currentWindow = $monthWindows[count($monthWindows) - 1];
        $pendingSnapshots[$currentWindow['key']] = $currentPending;
        $inProgressSnapshots[$currentWindow['key']] = $currentInProgress;

        return [
            'months' => $months,
            'windows' => array_map(static function (array $window) {
                return [
                    'key' => (string) $window['key'],
                    'label' => (string) $window['label'],
                ];
            }, $monthWindows),
            'new_counts' => $newCounts,
            'completed_counts' => $completedCounts,
            'pending_snapshots' => $pendingSnapshots,
            'in_progress_snapshots' => $inProgressSnapshots,
            'current_pending' => $currentPending,
            'current_in_progress' => $currentInProgress,
        ];
    }

    private function buildDatatableQuery(array $filters, ?string $moduleId = null): Builder
    {
        $actorUserId = (string) ($filters['actor_user_id'] ?? '');
        $actorRoles = (array) ($filters['actor_roles'] ?? []);
        $canViewAll = (bool) ($filters['can_view_all'] ?? false);

        $scope = trim((string) ($filters['scope'] ?? 'mine'));
        $archived = trim((string) ($filters['archived'] ?? 'active'));
        $status = trim((string) ($filters['status'] ?? ''));
        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));
        $assignedTo = trim((string) ($filters['assigned_to'] ?? ''));

        $query = $this->queryInModule($moduleId)->with(['assignee.profile']);

        if ($archived === 'all') {
            $query->withTrashed();
        } elseif ($archived === 'archived') {
            $query->onlyTrashed();
        }

        if ($scope === 'all' && $canViewAll) {
            // show all current-module records
        } elseif ($scope === 'available') {
            $this->applyEligibleRoleScope(
                $query->whereNull('assigned_to_user_id')
                    ->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS]),
                $actorRoles
            );
        } else {
            $query->where('assigned_to_user_id', $actorUserId);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function (Builder $qb) use ($search) {
                $qb->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('assignee', function (Builder $uq) use ($search) {
                        $uq->where('username', 'like', "%{$search}%")
                            ->orWhereHas('profile', function (Builder $pq) use ($search) {
                                $pq->where('full_name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($assignedTo !== '') {
            $query->whereHas('assignee', function (Builder $uq) use ($assignedTo) {
                $uq->where('username', 'like', "%{$assignedTo}%")
                    ->orWhereHas('profile', function (Builder $pq) use ($assignedTo) {
                        $pq->where('full_name', 'like', "%{$assignedTo}%");
                    });
            });
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            try {
                $from = Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay();
                $query->where('created_at', '>=', $from);
            } catch (\Throwable $_e) {
                // ignored after request validation
            }
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            try {
                $to = Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay();
                $query->where('created_at', '<=', $to);
            } catch (\Throwable $_e) {
                // ignored after request validation
            }
        }

        return $query;
    }

    private function applyDatatableSort(Builder $query, array $filters): Builder
    {
        $sortField = $filters['sorters'][0]['field'] ?? null;
        $sortDir = (($filters['sorters'][0]['dir'] ?? 'desc') === 'asc') ? 'asc' : 'desc';

        $map = [
            'title' => 'title',
            'status' => 'status',
            'created_at' => 'created_at',
        ];

        if ($sortField && isset($map[$sortField])) {
            return $query->orderBy($map[$sortField], $sortDir);
        }

        return $query->orderByDesc('created_at');
    }

    private function queryInModule(?string $moduleId = null): Builder
    {
        return Task::query()
            ->when($moduleId, function (Builder $query, string $moduleId) {
                $query->where('module_id', $moduleId);
            });
    }

    private function applyEligibleRoleScope(Builder $query, array $roles): Builder
    {
        return $query->where(function (Builder $qb) use ($roles) {
            $qb->whereNull('data->eligible_roles');

            foreach ($roles as $role) {
                $qb->orWhereJsonContains('data->eligible_roles', $role);
            }
        });
    }

    private function resolveStatusAtWindowEnd(Task $task, Carbon $windowEnd): ?string
    {
        if (! $task->created_at || $task->created_at->gt($windowEnd)) {
            return null;
        }

        if ($task->deleted_at && $task->deleted_at->lte($windowEnd)) {
            return null;
        }

        $status = Task::STATUS_PENDING;
        $hasStatusEvents = false;

        foreach ($task->events as $event) {
            if (! $event->created_at || $event->created_at->gt($windowEnd)) {
                break;
            }

            $nextStatus = trim((string) ($event->to_status ?? ''));
            if ($nextStatus === '') {
                continue;
            }

            $status = $nextStatus;
            $hasStatusEvents = true;
        }

        if ($hasStatusEvents) {
            return $status;
        }

        if ($task->cancelled_at && $task->cancelled_at->lte($windowEnd)) {
            return Task::STATUS_CANCELLED;
        }

        if ($task->completed_at && $task->completed_at->lte($windowEnd)) {
            return Task::STATUS_DONE;
        }

        if ($task->started_at && $task->started_at->lte($windowEnd)) {
            return Task::STATUS_IN_PROGRESS;
        }

        return Task::STATUS_PENDING;
    }
}
