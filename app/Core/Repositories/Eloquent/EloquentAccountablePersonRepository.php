<?php

namespace App\Core\Repositories\Eloquent;

use App\Core\Models\AccountablePerson;
use App\Core\Repositories\Contracts\AccountablePersonRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentAccountablePersonRepository implements AccountablePersonRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): AccountablePerson
    {
        $query = AccountablePerson::query()->with([
            'department' => fn ($departmentQuery) => $departmentQuery
                ->withTrashed()
                ->select(['id', 'code', 'name', 'short_name']),
        ]);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator
    {
        $query = AccountablePerson::query()->with([
            'department' => fn ($departmentQuery) => $departmentQuery
                ->withTrashed()
                ->select(['id', 'code', 'name', 'short_name']),
        ]);

        $archived = $this->resolveArchivedMode($filters);
        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));
        $departmentId = trim((string) ($filters['department_id'] ?? ''));

        if ($archived === 'all') {
            $query->withTrashed();
        } elseif ($archived === 'archived') {
            $query->onlyTrashed();
        }

        if ($departmentId !== '') {
            $query->where('department_id', $departmentId);
        }

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('full_name', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('office', 'like', "%{$search}%")
                    ->orWhereHas('department', function (Builder $departmentQuery) use ($search) {
                        $departmentQuery->withTrashed()
                            ->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('short_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query
            ->orderBy('full_name')
            ->paginate(
                perPage: max(1, min($size, 100)),
                columns: ['*'],
                pageName: 'page',
                page: max(1, $page),
            );
    }

    public function findByNormalizedName(string $normalizedName, ?string $excludeId = null, bool $withTrashed = false): ?AccountablePerson
    {
        $query = AccountablePerson::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        $query->where('normalized_name', $normalizedName);

        if ($excludeId !== null && trim($excludeId) !== '') {
            $query->whereKeyNot($excludeId);
        }

        return $query->first();
    }

    public function suggest(string $query, int $limit = 12): Collection
    {
        $search = trim($query);

        if ($search === '') {
            return collect();
        }

        return AccountablePerson::query()
            ->with([
                'department' => fn ($departmentQuery) => $departmentQuery
                    ->withTrashed()
                    ->select(['id', 'code', 'name', 'short_name']),
            ])
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('full_name', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('office', 'like', "%{$search}%");
            })
            ->orderBy('full_name')
            ->limit(max(1, min($limit, 50)))
            ->get();
    }

    public function create(array $data): AccountablePerson
    {
        return AccountablePerson::query()->create($data)->load('department');
    }

    public function save(AccountablePerson $accountablePerson): AccountablePerson
    {
        $accountablePerson->save();

        return $accountablePerson->refresh()->load('department');
    }

    public function delete(AccountablePerson $accountablePerson): void
    {
        $accountablePerson->delete();
    }

    public function restore(AccountablePerson $accountablePerson): void
    {
        $accountablePerson->restore();
    }

    private function resolveArchivedMode(array $filters): string
    {
        $mode = trim((string) ($filters['archived'] ?? $filters['status'] ?? 'active'));

        if (in_array($mode, ['active', 'archived', 'all'], true)) {
            return $mode;
        }

        return 'active';
    }
}
