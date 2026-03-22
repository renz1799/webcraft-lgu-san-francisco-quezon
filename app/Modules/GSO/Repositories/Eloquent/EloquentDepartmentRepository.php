<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Core\Models\Department;
use App\Modules\GSO\Repositories\Contracts\DepartmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentDepartmentRepository implements DepartmentRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): Department
    {
        $query = Department::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator
    {
        $query = Department::query();
        $archived = $this->resolveArchivedMode($filters);
        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));

        if ($archived === 'all') {
            $query->withTrashed();
        } elseif ($archived === 'archived') {
            $query->onlyTrashed();
        }

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('short_name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy('code')
            ->paginate(
                perPage: max(1, min($size, 100)),
                columns: ['*'],
                pageName: 'page',
                page: max(1, $page),
            );
    }

    public function activeOptions(): Collection
    {
        return Department::query()
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->select(['id', 'code', 'name', 'short_name'])
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Department
    {
        return Department::query()->create($data);
    }

    public function save(Department $department): Department
    {
        $department->save();

        return $department->refresh();
    }

    public function delete(Department $department): void
    {
        $department->delete();
    }

    public function restore(Department $department): void
    {
        $department->restore();
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
