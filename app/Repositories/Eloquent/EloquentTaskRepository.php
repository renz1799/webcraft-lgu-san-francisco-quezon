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
}
