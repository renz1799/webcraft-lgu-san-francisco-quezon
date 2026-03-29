<?php

namespace App\Core\Repositories\Eloquent;

use App\Core\Models\UserIdentityChangeRequest;
use App\Core\Repositories\Contracts\UserIdentityChangeRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentUserIdentityChangeRequestRepository implements UserIdentityChangeRequestRepositoryInterface
{
    public function create(array $attributes): UserIdentityChangeRequest
    {
        return UserIdentityChangeRequest::query()->create($attributes);
    }

    public function save(UserIdentityChangeRequest $request): UserIdentityChangeRequest
    {
        $request->save();

        return $request->refresh();
    }

    public function findById(string $id): ?UserIdentityChangeRequest
    {
        return $this->baseQuery()->find($id);
    }

    public function findPendingForUser(string $userId): ?UserIdentityChangeRequest
    {
        return $this->baseQuery()
            ->where('user_id', $userId)
            ->where('status', UserIdentityChangeRequest::STATUS_PENDING)
            ->latest('created_at')
            ->first();
    }

    public function findLatestForUser(string $userId): ?UserIdentityChangeRequest
    {
        return $this->baseQuery()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->first();
    }

    public function paginateForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->baseQuery();

        $moduleIds = collect((array) ($filters['module_ids'] ?? []))
            ->map(fn ($moduleId) => trim((string) $moduleId))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($moduleIds !== []) {
            $query->whereHas('user.userModules', function (Builder $membershipQuery) use ($moduleIds) {
                $membershipQuery->whereIn('module_id', $moduleIds)
                    ->where('is_active', true);
            });
        }

        $status = trim((string) ($filters['status'] ?? 'pending'));
        if ($status !== '' && $status !== 'all') {
            $query->where('status', $status);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function (Builder $sub) use ($search) {
                $sub->whereHas('user', function (Builder $userQuery) use ($search) {
                    $userQuery->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', function (Builder $profileQuery) use ($search) {
                            $profileQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('name_extension', 'like', "%{$search}%");
                        });
                })->orWhere('requested_first_name', 'like', "%{$search}%")
                    ->orWhere('requested_middle_name', 'like', "%{$search}%")
                    ->orWhere('requested_last_name', 'like', "%{$search}%")
                    ->orWhere('requested_name_extension', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    private function baseQuery(): Builder
    {
        return UserIdentityChangeRequest::query()->with([
            'user:id,primary_department_id,username,email,is_active',
            'user.profile:id,user_id,first_name,middle_name,last_name,name_extension',
            'user.primaryDepartment:id,code,name,short_name',
            'reviewer:id,username,email',
            'reviewer.profile:id,user_id,first_name,middle_name,last_name,name_extension',
        ]);
    }
}
