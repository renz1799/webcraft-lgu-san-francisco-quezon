<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class EloquentUserRepository implements UserRepositoryInterface
{
    /** Create a user */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /** Update a user */
    public function update(User $user, array $data): User
    {
        $user->fill($data)->save();

        return $user->refresh();
    }

    /** Find by id (active only) */
    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    /** Find by id incl. soft-deleted */
    public function findByIdWithTrashed(string $id): ?User
    {
        return User::withTrashed()->find($id);
    }

    /** Find by email */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /** List users (simple paginate) */
    public function paginate(int $perPage = 30): LengthAwarePaginator
    {
        return User::paginate($perPage);
    }

    /** Soft-delete */
    public function delete(User $user): void
    {
        $user->delete();
    }

    /** Restore soft-deleted */
    public function restore(User $user): bool
    {
        return (bool) $user->restore();
    }

    /** Permanently delete */
    public function forceDelete(User $user): bool
    {
        return (bool) $user->forceDelete();
    }

    /** Convenience: restore by id */
    public function restoreById(string $id): bool
    {
        $user = User::withTrashed()->find($id);

        return $user ? (bool) $user->restore() : false;
    }

    /**
     * Assign a role (resolve by UUID or name) and sync default role permissions as directs.
     * NOTE: Spatie already grants role permissions via Gate; syncing as directs keeps UI toggles in sync.
     */
    public function assignRoleAndSyncPermissions(User $user, string $roleInput): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Str::isUuid($roleInput)
            ? Role::findById($roleInput, 'web')
            : Role::findByName($roleInput, 'web');

        $user->syncRoles([]);
        $user->assignRole($role->name);

        if ($role->permissions->isNotEmpty()) {
            $user->syncPermissions($role->permissions);
        } else {
            $user->syncPermissions([]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        $page = max(1, (int) $page);
        $size = max(1, min((int) $size, 100));
        $archivedMode = $this->resolveArchivedMode($filters);

        $recordsTotal = (clone $this->buildBaseDatatableQuery($archivedMode))->count();

        $filteredForCount = $this->buildFilteredDatatableQuery($filters);
        $recordsFiltered = (clone $filteredForCount)->count();

        $lastPage = $recordsFiltered > 0 ? (int) ceil($recordsFiltered / $size) : 1;
        $page = min($page, $lastPage);

        $rows = $this->applyDatatableSort(
            $this->buildFilteredDatatableQuery($filters),
            $filters
        )
            ->forPage($page, $size)
            ->get()
            ->map(fn (User $user) => $this->mapDatatableRow($user))
            ->values()
            ->all();

        return [
            'data' => $rows,
            'last_page' => $lastPage,
            'total' => (int) $recordsFiltered,
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
        ];
    }

    public function listForTaskReassign(): array
    {
        return User::query()
            ->with(['profile:id,user_id,first_name,middle_name,last_name,name_extension'])
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('username')
            ->get(['id', 'username'])
            ->map(function (User $u) {
                $name = $u->profile?->full_name ?: ($u->username ?: 'Unknown User');

                return [
                    'id' => (string) $u->id,
                    'name' => trim((string) $name),
                ];
            })
            ->values()
            ->all();
    }

    public function getUserIdsByRoles(array $roleNames): array
    {
        return User::role($roleNames)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->values()
            ->all();
    }

    private function buildBaseDatatableQuery(string $archivedMode = 'active'): Builder
    {
        $q = User::query()
            ->with(['roles:id,name'])
            ->select(['id', 'username', 'email', 'is_active', 'created_at', 'deleted_at', 'user_type'])
            ->where('user_type', '!=', 'Administrator');

        if ($archivedMode === 'all') {
            $q->withTrashed();
        } elseif ($archivedMode === 'archived') {
            $q->onlyTrashed();
        }

        return $q;
    }

    private function buildFilteredDatatableQuery(array $filters): Builder
    {
        $q = $this->buildBaseDatatableQuery($this->resolveArchivedMode($filters));

        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));
        if ($search !== '') {
            $q->where(function (Builder $sub) use ($search) {
                $sub->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('roles', function (Builder $rq) use ($search) {
                        $rq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $username = trim((string) ($filters['username'] ?? ''));
        if ($username !== '') {
            $q->where('username', 'like', "%{$username}%");
        }

        $email = trim((string) ($filters['email'] ?? ''));
        if ($email !== '') {
            $q->where('email', 'like', "%{$email}%");
        }

        $role = trim((string) ($filters['role'] ?? ''));
        if ($role !== '') {
            $q->whereHas('roles', function (Builder $rq) use ($role) {
                $rq->where('name', 'like', "%{$role}%");
            });
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status === 'active') {
            $q->where('is_active', true);
        } elseif ($status === 'inactive') {
            $q->where('is_active', false);
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            try {
                $from = Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay();
                $q->where('created_at', '>=', $from);
            } catch (\Throwable $_e) {
                // ignored after request validation
            }
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            try {
                $to = Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay();
                $q->where('created_at', '<=', $to);
            } catch (\Throwable $_e) {
                // ignored after request validation
            }
        }

        return $q;
    }

    private function resolveArchivedMode(array $filters): string
    {
        $mode = trim((string) ($filters['archived'] ?? 'active'));

        if (in_array($mode, ['active', 'archived', 'all'], true)) {
            return $mode;
        }

        return 'active';
    }

    private function applyDatatableSort(Builder $q, array $filters): Builder
    {
        $sortField = $filters['sorters'][0]['field'] ?? null;
        $sortDir = (($filters['sorters'][0]['dir'] ?? 'desc') === 'asc') ? 'asc' : 'desc';

        $map = [
            'username' => 'username',
            'email' => 'email',
            'is_active' => 'is_active',
            'created_at' => 'created_at',
        ];

        if ($sortField && isset($map[$sortField])) {
            return $q->orderBy($map[$sortField], $sortDir);
        }

        return $q->orderByDesc('created_at');
    }

    private function mapDatatableRow(User $user): array
    {
        $isArchived = $user->deleted_at !== null;

        return [
            'id' => (string) $user->id,
            'username' => (string) ($user->username ?? '-'),
            'email' => (string) ($user->email ?? '-'),
            'role' => optional($user->roles->first())->name ?? 'No Role Assigned',
            'created_at' => $user->created_at?->toDateTimeString(),
            'created_at_text' => $user->created_at?->format('M d, Y h:i A') ?? '-',
            'is_active' => (bool) $user->is_active,
            'is_archived' => $isArchived,
            'edit_url' => $isArchived ? null : route('access.users.edit', $user),
            'status_url' => $isArchived ? null : route('access.users.status.update', $user),
            'delete_url' => $isArchived ? null : route('access.users.destroy', $user),
            'restore_url' => $isArchived ? route('access.users.restore', $user) : null,
            'force_delete_url' => $isArchived ? route('access.users.force-delete', $user) : null,
        ];
    }
}
