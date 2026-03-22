<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\Inspection;
use App\Modules\GSO\Repositories\Contracts\InspectionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentInspectionRepository implements InspectionRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): Inspection
    {
        $query = Inspection::query()
            ->with($this->relations())
            ->withCount('photos');

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator
    {
        $query = Inspection::query()
            ->with($this->relations())
            ->withCount('photos');
        $archived = $this->resolveArchivedMode($filters);
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $departmentId = trim((string) ($filters['department_id'] ?? ''));
        $itemId = trim((string) ($filters['item_id'] ?? ''));

        if ($archived === 'all') {
            $query->withTrashed();
        } elseif ($archived === 'archived') {
            $query->onlyTrashed();
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($departmentId !== '') {
            $query->where('department_id', $departmentId);
        }

        if ($itemId !== '') {
            $query->where('item_id', $itemId);
        }

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('po_number', 'like', "%{$search}%")
                    ->orWhere('dv_number', 'like', "%{$search}%")
                    ->orWhere('observed_description', 'like', "%{$search}%")
                    ->orWhere('item_name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('office_department', 'like', "%{$search}%")
                    ->orWhere('accountable_officer', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhereHas('item', function (Builder $itemQuery) use ($search) {
                        $itemQuery->withTrashed()
                            ->where(function (Builder $itemSearch) use ($search) {
                                $itemSearch->where('item_name', 'like', "%{$search}%")
                                    ->orWhere('item_identification', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('department', function (Builder $departmentQuery) use ($search) {
                        $departmentQuery->withTrashed()
                            ->where(function (Builder $departmentSearch) use ($search) {
                                $departmentSearch->where('code', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        return $query
            ->orderByDesc('created_at')
            ->paginate(
                perPage: max(1, min($size, 100)),
                columns: ['*'],
                pageName: 'page',
                page: max(1, $page),
            );
    }

    public function create(array $data): Inspection
    {
        return Inspection::query()->create($data)->load($this->relations());
    }

    public function save(Inspection $inspection): Inspection
    {
        $inspection->save();

        return $inspection->refresh()->load($this->relations());
    }

    public function delete(Inspection $inspection): void
    {
        $inspection->delete();
    }

    public function restore(Inspection $inspection): void
    {
        $inspection->restore();
    }

    private function relations(): array
    {
        return [
            'item' => fn ($itemQuery) => $itemQuery
                ->withTrashed()
                ->select(['id', 'item_name', 'item_identification']),
            'department' => fn ($departmentQuery) => $departmentQuery
                ->withTrashed()
                ->select(['id', 'code', 'name']),
            'inspector' => fn ($userQuery) => $userQuery
                ->withTrashed()
                ->select(['id', 'username', 'email']),
            'reviewer' => fn ($userQuery) => $userQuery
                ->withTrashed()
                ->select(['id', 'username', 'email']),
        ];
    }

    private function resolveArchivedMode(array $filters): string
    {
        $mode = trim((string) ($filters['archived'] ?? $filters['record_status'] ?? 'active'));

        if (in_array($mode, ['active', 'archived', 'all'], true)) {
            return $mode;
        }

        return 'active';
    }
}
