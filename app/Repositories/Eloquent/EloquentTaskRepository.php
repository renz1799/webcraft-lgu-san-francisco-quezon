<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function findLatestBySubject(string $subjectType, string $subjectId): ?Task
    {
        return Task::query()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->orderByDesc('created_at')
            ->first();
    }

    public function findOrFail(string $id): Task
    {
        return Task::query()->findOrFail($id);
    }

    public function findOrFailWithTrashed(string $id): Task
    {
        return Task::withTrashed()->findOrFail($id);
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
        return Task::query()
            ->whereNull('assigned_to_user_id')
            ->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS])
            ->where(function (Builder $q) use ($roles) {
                $q->whereNull('data->eligible_roles');

                foreach ($roles as $role) {
                    $q->orWhereJsonContains('data->eligible_roles', $role);
                }
            })
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        $page = max(1, (int) $page);
        $size = max(1, min((int) $size, 100));

        $query = $this->buildDatatableQuery($filters);

        $total = (clone $query)->count();
        $lastPage = $total > 0 ? (int) ceil($total / $size) : 1;
        $page = min($page, $lastPage);

        $rows = $this->applyDatatableSort($query, $filters)
            ->forPage($page, $size)
            ->get()
            ->map(fn (Task $task) => $this->mapDatatableRow($task, $filters))
            ->values()
            ->all();

        return [
            'data' => $rows,
            'last_page' => $lastPage,
            'total' => (int) $total,
        ];
    }

    public function countsForSidebar(string $userId, array $roles): array
    {
        $my = Task::query()
            ->where('assigned_to_user_id', $userId)
            ->whereNotIn('status', [Task::STATUS_DONE, Task::STATUS_CANCELLED])
            ->count();

        $claimable = Task::query()
            ->whereNull('assigned_to_user_id')
            ->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS])
            ->where(function (Builder $qb) use ($roles) {
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

    public function adminDashboardStats(int $months = 6): array
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

        $tasks = Task::query()
            ->select([
                'id',
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

        $currentWindow = $monthWindows[count($monthWindows) - 1];
        $previousWindow = $monthWindows[count($monthWindows) - 2];

        $currentPending = Task::query()
            ->where('status', Task::STATUS_PENDING)
            ->count();

        $currentInProgress = Task::query()
            ->where('status', Task::STATUS_IN_PROGRESS)
            ->count();

        $pendingSnapshots[$currentWindow['key']] = $currentPending;
        $inProgressSnapshots[$currentWindow['key']] = $currentInProgress;

        return [
            'period_label' => "Last {$months} months",
            'cards' => [
                'new' => $this->makeAdminMetricCard(
                    current: (int) $newCounts[$currentWindow['key']],
                    previous: (int) $newCounts[$previousWindow['key']],
                    contextLabel: 'this month',
                    comparisonLabel: 'Prev Month'
                ),
                'completed' => $this->makeAdminMetricCard(
                    current: (int) $completedCounts[$currentWindow['key']],
                    previous: (int) $completedCounts[$previousWindow['key']],
                    contextLabel: 'this month',
                    comparisonLabel: 'Prev Month'
                ),
                'pending' => $this->makeAdminMetricCard(
                    current: $currentPending,
                    previous: (int) $pendingSnapshots[$previousWindow['key']],
                    contextLabel: 'open right now',
                    comparisonLabel: 'End of Last Month'
                ),
                'in_progress' => $this->makeAdminMetricCard(
                    current: $currentInProgress,
                    previous: (int) $inProgressSnapshots[$previousWindow['key']],
                    contextLabel: 'open right now',
                    comparisonLabel: 'End of Last Month'
                ),
            ],
            'chart' => [
                'categories' => array_column($monthWindows, 'label'),
                'series' => [
                    [
                        'name' => 'New',
                        'data' => array_values($newCounts),
                    ],
                    [
                        'name' => 'Pending',
                        'data' => array_values($pendingSnapshots),
                    ],
                    [
                        'name' => 'Completed',
                        'data' => array_values($completedCounts),
                    ],
                    [
                        'name' => 'Inprogress',
                        'data' => array_values($inProgressSnapshots),
                    ],
                ],
            ],
        ];
    }

    private function buildDatatableQuery(array $filters): Builder
    {
        $actorUserId = (string) ($filters['actor_user_id'] ?? '');
        $actorRoles = (array) ($filters['actor_roles'] ?? []);
        $canViewAll = (bool) ($filters['can_view_all'] ?? false);

        $scope = trim((string) ($filters['scope'] ?? 'mine'));
        $archived = trim((string) ($filters['archived'] ?? 'active'));
        $status = trim((string) ($filters['status'] ?? ''));
        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));
        $assignedTo = trim((string) ($filters['assigned_to'] ?? ''));

        $query = Task::query()->with(['assignee.profile']);

        if ($archived === 'all') {
            $query->withTrashed();
        } elseif ($archived === 'archived') {
            $query->onlyTrashed();
        }

        if ($scope === 'all' && $canViewAll) {
            // show all records
        } elseif ($scope === 'available') {
            $query
                ->whereNull('assigned_to_user_id')
                ->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS])
                ->where(function (Builder $qb) use ($actorRoles) {
                    $qb->whereNull('data->eligible_roles');

                    foreach ($actorRoles as $role) {
                        $qb->orWhereJsonContains('data->eligible_roles', $role);
                    }
                });
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
                        $uq->where('username', 'like', "%{$search}%");
                    });
            });
        }

        if ($assignedTo !== '') {
            $query->whereHas('assignee', function (Builder $uq) use ($assignedTo) {
                $uq->where('username', 'like', "%{$assignedTo}%");
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

    private function mapDatatableRow(Task $task, array $filters): array
    {
        $isArchived = $task->deleted_at !== null;
        $actorRoles = (array) ($filters['actor_roles'] ?? []);
        $canArchive = (bool) ($filters['can_archive'] ?? false);

        $canClaim = ! $isArchived
            && empty($task->assigned_to_user_id)
            && in_array((string) $task->status, [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS], true)
            && $this->isEligibleForRoles($task, $actorRoles);

        return [
            'id' => (string) $task->id,
            'title' => (string) ($task->title ?? '-'),
            'status' => (string) ($task->status ?? '-'),
            'assigned_to_name' => (string) ($task->assigned_to_name ?? '-'),
            'created_at' => $task->created_at?->toDateTimeString(),
            'created_at_text' => $task->created_at?->format('M d, Y h:i A') ?? '-',
            'is_archived' => $isArchived,
            'show_url' => route('tasks.show', ['id' => (string) $task->id]),
            'claim_url' => $canClaim ? route('tasks.claim', ['id' => (string) $task->id]) : null,
            'archive_url' => ($canArchive && ! $isArchived)
                ? route('tasks.destroy', ['id' => (string) $task->id])
                : null,
            'restore_url' => ($canArchive && $isArchived)
                ? route('tasks.restore', ['id' => (string) $task->id])
                : null,
        ];
    }

    private function isEligibleForRoles(Task $task, array $roles): bool
    {
        $eligible = (array) data_get($task->data, 'eligible_roles', []);

        if (count($eligible) === 0) {
            return true;
        }

        return count(array_intersect($roles, $eligible)) > 0;
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

    private function makeAdminMetricCard(
        int $current,
        int $previous,
        string $contextLabel,
        string $comparisonLabel
    ): array {
        $delta = $current - $previous;
        $direction = $delta === 0
            ? 'flat'
            : ($delta > 0 ? 'up' : 'down');

        $deltaPercent = $previous === 0
            ? ($current === 0 ? 0.0 : 100.0)
            : round((abs($delta) / $previous) * 100, 2);

        return [
            'value' => $current,
            'comparison_value' => $previous,
            'comparison_label' => $comparisonLabel,
            'context_label' => $contextLabel,
            'delta_percent' => $deltaPercent,
            'direction' => $direction,
        ];
    }
}