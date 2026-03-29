<?php

namespace App\Core\Repositories\Eloquent;

use App\Core\Models\Role;
use App\Core\Models\User;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Builders\Contracts\User\UserDatatableRowBuilderInterface;
use App\Core\Builders\Contracts\User\UserDatatableActionBuilderInterface;
use App\Core\Support\AdminRouteResolver;
use Carbon\Carbon;
use App\Core\Support\CurrentContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly UserDatatableRowBuilderInterface $userDatatableRowBuilder,
        private readonly UserDatatableActionBuilderInterface $userDatatableActionBuilder,
        private readonly CurrentContext $context,
        private readonly ?AdminRouteResolver $adminRoutes = null,
    ) {}

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

    /** Convenience: restore by id */
    public function restoreById(string $id): bool
    {
        $user = User::withTrashed()->find($id);

        return $user ? (bool) $user->restore() : false;
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
            ->map(function (User $user) {
                return array_merge(
                    $this->userDatatableRowBuilder->build($user),
                    $this->userDatatableActionBuilder->build($user),
                );
            })
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

    public function findForPlatformAccessOverview(string $id): ?User
    {
        return User::query()
            ->withTrashed()
            ->select([
                'users.id',
                'users.username',
                'users.email',
                'users.is_active',
                'users.primary_department_id',
                'users.created_at',
                'users.deleted_at',
                'users.user_type',
            ])
            ->selectSub(function ($query) {
                $query->from('login_details')
                    ->select('created_at')
                    ->whereColumn('login_details.user_id', 'users.id')
                    ->where('success', true)
                    ->orderByDesc('created_at')
                    ->limit(1);
            }, 'last_login_at')
            ->with([
                'profile:id,user_id,first_name,middle_name,last_name,name_extension',
                'primaryDepartment:id,code,name,short_name',
                'userModules' => function ($query) {
                    $query->with([
                        'module:id,code,name,type,is_active',
                        'department:id,code,name,short_name',
                    ]);
                },
                'moduleRoleAssignments' => function ($query) {
                    $query->with([
                        'role:id,name,module_id',
                        'module:id,code,name,type,is_active',
                    ]);
                },
            ])
            ->find($id);
    }

    public function findInModule(string $id, string $moduleId): ?User
    {
        return $this->buildModuleUserQuery($moduleId)
            ->whereKey($id)
            ->first();
    }

    public function findActiveInModule(string $id, string $moduleId): ?User
    {
        return $this->buildModuleUserQuery($moduleId, activeOnly: true)
            ->whereKey($id)
            ->where('users.is_active', true)
            ->first();
    }

    public function getActiveUsersForModule(string $moduleId): Collection
    {
        return $this->buildModuleUserQuery($moduleId, activeOnly: true)
            ->where('users.is_active', true)
            ->orderBy('users.username')
            ->get(['users.id', 'users.username']);
    }

    public function getUserIdsByRoles(array $roleNames): array
    {
        return $this->getUserIdsByRolesInModule($roleNames, $this->requireModuleId());
    }

    public function getUserIdsByRolesInModule(array $roleNames, string $moduleId): array
    {
        $requestedRoleNames = array_values(array_unique(array_filter(
            array_map(static fn ($roleName) => trim((string) $roleName), $roleNames)
        )));

        if ($requestedRoleNames === []) {
            return [];
        }

        $existingRoleNames = Role::query()
            ->where('module_id', $moduleId)
            ->where('guard_name', 'web')
            ->whereNull('deleted_at')
            ->whereIn('name', $requestedRoleNames)
            ->pluck('name')
            ->map(fn ($name) => (string) $name)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $missingRoleNames = array_values(array_diff($requestedRoleNames, $existingRoleNames));

        if ($missingRoleNames !== []) {
            Log::warning('User role lookup skipped missing roles.', [
                'module_id' => $moduleId,
                'guard_name' => 'web',
                'requested_roles' => $requestedRoleNames,
                'missing_roles' => $missingRoleNames,
            ]);
        }

        if ($existingRoleNames === []) {
            return [];
        }

        return User::query()
            ->select('users.id')
            ->join('model_has_roles', function ($join) use ($moduleId) {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', '=', User::class)
                    ->where('model_has_roles.module_id', '=', $moduleId);
            })
            ->join('roles', function ($join) use ($moduleId, $existingRoleNames) {
                $join->on('roles.id', '=', 'model_has_roles.role_id')
                    ->where('roles.module_id', '=', $moduleId)
                    ->where('roles.guard_name', '=', 'web')
                    ->whereNull('roles.deleted_at')
                    ->whereIn('roles.name', $existingRoleNames);
            })
            ->distinct()
            ->pluck('users.id')
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->values()
            ->all();
    }

    public function getRoleNamesInModule(User $user, string $moduleId): array
    {
        $moduleId = trim($moduleId);

        if ($moduleId === '') {
            return [];
        }

        return Role::query()
            ->select('roles.name')
            ->join('model_has_roles', function ($join) use ($user, $moduleId) {
                $join->on('model_has_roles.role_id', '=', 'roles.id')
                    ->where('model_has_roles.model_type', '=', User::class)
                    ->where('model_has_roles.model_id', '=', $user->id)
                    ->where('model_has_roles.module_id', '=', $moduleId);
            })
            ->where('roles.module_id', $moduleId)
            ->where('roles.guard_name', 'web')
            ->whereNull('roles.deleted_at')
            ->pluck('roles.name')
            ->map(fn ($name) => (string) $name)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function buildBaseDatatableQuery(string $archivedMode = 'active'): Builder
    {
        $moduleScoped = $this->isModuleScoped();

        $q = User::query()
            ->select([
                'users.id',
                'users.username',
                'users.email',
                'users.is_active',
                'users.primary_department_id',
                'users.created_at',
                'users.deleted_at',
                'users.user_type',
            ])
            ->selectSub(function ($query) {
                $query->from('login_details')
                    ->select('created_at')
                    ->whereColumn('login_details.user_id', 'users.id')
                    ->where('success', true)
                    ->orderByDesc('created_at')
                    ->limit(1);
            }, 'last_login_at');

        if ($moduleScoped) {
            $moduleId = $this->requireModuleId();

            $q->with(['moduleRoleAssignments' => function ($query) use ($moduleId) {
                $query->where('module_id', $moduleId)
                    ->with(['role:id,name,module_id']);
            }]);

            $q->where('users.user_type', '!=', 'Administrator');
            $q->selectSub(function ($query) use ($moduleId) {
                $query->from('user_modules')
                    ->selectRaw('CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END')
                    ->whereColumn('user_modules.user_id', 'users.id')
                    ->where('user_modules.module_id', $moduleId)
                    ->where('user_modules.is_active', true);
            }, 'current_module_membership_is_active');

            $q->whereHas('userModules', function (Builder $query) use ($moduleId) {
                $query->where('module_id', $moduleId)
                    ->whereNotNull('user_modules.id');
            });
        } else {
            $q->with([
                'profile:id,user_id,first_name,middle_name,last_name,name_extension',
                'primaryDepartment:id,code,name,short_name',
                'userModules' => function ($query) {
                    $query->where('is_active', true)
                        ->with([
                            'module:id,code,name,type,is_active',
                            'department:id,code,name,short_name',
                        ]);
                },
                'moduleRoleAssignments' => function ($query) {
                    $query->with([
                        'role:id,name,module_id',
                        'module:id,code,name,type,is_active',
                    ]);
                },
            ]);
        }

        if ($archivedMode === 'all') {
            $q->withTrashed();
        } elseif ($archivedMode === 'archived') {
            $q->onlyTrashed();
        }

        return $q;
    }

    private function buildFilteredDatatableQuery(array $filters): Builder
    {
        $moduleScoped = $this->isModuleScoped();
        $moduleId = $moduleScoped ? $this->requireModuleId() : null;
        $q = $this->buildBaseDatatableQuery($this->resolveArchivedMode($filters));

        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));
        if ($search !== '') {
            $q->where(function (Builder $sub) use ($search, $moduleScoped, $moduleId) {
                $sub->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");

                if ($moduleScoped) {
                    $sub->orWhereHas('moduleRoleAssignments.role', function (Builder $rq) use ($search, $moduleId) {
                        $rq->where('roles.module_id', $moduleId)
                            ->where('name', 'like', "%{$search}%");
                    });

                    return;
                }

                $sub->orWhereHas('profile', function (Builder $profileQuery) use ($search) {
                    $profileQuery
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('name_extension', 'like', "%{$search}%");
                })->orWhereHas('primaryDepartment', function (Builder $departmentQuery) use ($search) {
                    $departmentQuery
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('short_name', 'like', "%{$search}%");
                })->orWhereHas('userModules', function (Builder $membershipQuery) use ($search) {
                    $membershipQuery->where('is_active', true)
                        ->where(function (Builder $activeMembershipQuery) use ($search) {
                            $activeMembershipQuery
                                ->whereHas('module', function (Builder $moduleQuery) use ($search) {
                                    $moduleQuery
                                        ->where('code', 'like', "%{$search}%")
                                        ->orWhere('name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('department', function (Builder $departmentQuery) use ($search) {
                                    $departmentQuery
                                        ->where('code', 'like', "%{$search}%")
                                        ->orWhere('name', 'like', "%{$search}%")
                                        ->orWhere('short_name', 'like', "%{$search}%");
                                });
                        });
                })->orWhereHas('moduleRoleAssignments', function (Builder $assignmentQuery) use ($search) {
                    $assignmentQuery
                        ->whereHas('role', function (Builder $roleQuery) use ($search) {
                            $roleQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('module', function (Builder $moduleQuery) use ($search) {
                            $moduleQuery
                                ->where('code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
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

        if ($moduleScoped) {
            $role = trim((string) ($filters['role'] ?? ''));
            if ($role !== '') {
                $q->whereHas('moduleRoleAssignments.role', function (Builder $rq) use ($role, $moduleId) {
                    $rq->where('roles.module_id', $moduleId)
                        ->where('name', 'like', "%{$role}%");
                });
            }

            $status = trim((string) ($filters['status'] ?? ''));
            if ($status === 'active') {
                $q->whereHas('userModules', function (Builder $query) use ($moduleId) {
                    $query->where('module_id', $moduleId)
                        ->where('is_active', true);
                });
            } elseif ($status === 'inactive') {
                $q->whereHas('userModules', function (Builder $query) use ($moduleId) {
                    $query->where('module_id', $moduleId);
                })->whereDoesntHave('userModules', function (Builder $query) use ($moduleId) {
                    $query->where('module_id', $moduleId)
                        ->where('is_active', true);
                });
            }
        } else {
            $moduleRole = trim((string) ($filters['module_role'] ?? ''));
            if ($moduleRole !== '') {
                $q->whereHas('moduleRoleAssignments.role', function (Builder $roleQuery) use ($moduleRole) {
                    $roleQuery->where('name', 'like', "%{$moduleRole}%");
                });
            }

            $moduleAccess = trim((string) ($filters['module_access'] ?? ''));
            if ($moduleAccess !== '') {
                $q->whereHas('userModules', function (Builder $membershipQuery) use ($moduleAccess) {
                    $membershipQuery->where('module_id', $moduleAccess)
                        ->where('is_active', true);
                });
            }

            if ((bool) ($filters['no_module_access'] ?? false)) {
                $q->whereDoesntHave('userModules', function (Builder $membershipQuery) {
                    $membershipQuery->where('is_active', true);
                });
            }

            if ((bool) ($filters['multi_module_only'] ?? false)) {
                $q->whereHas('userModules', function (Builder $membershipQuery) {
                    $membershipQuery->where('is_active', true);
                }, '>=', 2);
            }

            $platformStatus = trim((string) ($filters['platform_status'] ?? ''));
            if ($platformStatus === 'active') {
                $q->where('users.is_active', true);
            } elseif ($platformStatus === 'inactive') {
                $q->where('users.is_active', false);
            }
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
        $moduleScoped = $this->isModuleScoped();

        $map = [
            'username' => 'users.username',
            'email' => 'users.email',
            'is_active' => $moduleScoped ? 'current_module_membership_is_active' : 'users.is_active',
            'last_login_at' => 'users.last_login_at',
            'created_at' => 'users.created_at',
        ];

        if ($sortField && isset($map[$sortField])) {
            return $q->orderBy($map[$sortField], $sortDir);
        }

        return $q->orderByDesc('users.created_at');
    }

    private function requireModuleId(): string
    {
        $moduleId = $this->context->moduleId();

        if (! $moduleId) {
            throw new \RuntimeException('Current module context is not available.');
        }

        return $moduleId;
    }

    private function buildModuleUserQuery(string $moduleId, bool $activeOnly = false): Builder
    {
        return User::query()
            ->with(['profile:id,user_id,first_name,middle_name,last_name,name_extension'])
            ->whereNull('deleted_at')
            ->whereHas('userModules', function (Builder $query) use ($moduleId) {
                $query->where('module_id', $moduleId);
            })
            ->when($activeOnly, function (Builder $builder) use ($moduleId) {
                $builder->whereHas('userModules', function (Builder $query) use ($moduleId) {
                    $query->where('module_id', $moduleId)
                        ->where('is_active', true);
                });
            });
    }

    private function isModuleScoped(): bool
    {
        return ($this->adminRoutes ?? app(AdminRouteResolver::class))->isModuleScoped();
    }

}
