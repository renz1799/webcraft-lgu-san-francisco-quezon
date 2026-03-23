<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Repositories\Contracts\AirRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentAirRepository implements AirRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): Air
    {
        $query = Air::query()->with($this->relations());

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator
    {
        $query = Air::query()->with($this->relations());
        $archived = $this->resolveArchivedMode($filters);
        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));
        $supplier = trim((string) ($filters['supplier'] ?? ''));
        $status = trim((string) ($filters['status'] ?? $filters['inspection_status'] ?? ''));
        $department = trim((string) ($filters['department'] ?? ''));
        $departmentId = trim((string) ($filters['department_id'] ?? ''));
        $fundSourceId = trim((string) ($filters['fund_source_id'] ?? ''));
        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        $receivedCompleteness = trim((string) ($filters['received_completeness'] ?? ''));

        if ($archived === 'all') {
            $query->withTrashed();
        } elseif ($archived === 'archived') {
            $query->onlyTrashed();
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($supplier !== '') {
            $query->where('supplier_name', 'like', "%{$supplier}%");
        }

        if ($departmentId !== '') {
            $query->where('requesting_department_id', $departmentId);
        }

        if ($department !== '') {
            $query->where(function (Builder $subQuery) use ($department) {
                $subQuery->where('requesting_department_name_snapshot', 'like', "%{$department}%")
                    ->orWhere('requesting_department_code_snapshot', 'like', "%{$department}%")
                    ->orWhereHas('department', function (Builder $departmentQuery) use ($department) {
                        $departmentQuery->withTrashed()
                            ->where(function (Builder $departmentSearch) use ($department) {
                                $departmentSearch->where('code', 'like', "%{$department}%")
                                    ->orWhere('name', 'like', "%{$department}%");
                            });
                    });
            });
        }

        if ($fundSourceId !== '') {
            $query->where('fund_source_id', $fundSourceId);
        }

        if ($receivedCompleteness !== '') {
            $query->where('received_completeness', $receivedCompleteness);
        }

        if ($dateFrom !== '') {
            $query->whereDate('air_date', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('air_date', '<=', $dateTo);
        }

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('air_number', 'like', "%{$search}%")
                    ->orWhere('po_number', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%")
                    ->orWhere('requesting_department_name_snapshot', 'like', "%{$search}%")
                    ->orWhere('requesting_department_code_snapshot', 'like', "%{$search}%")
                    ->orWhere('inspected_by_name', 'like', "%{$search}%")
                    ->orWhere('accepted_by_name', 'like', "%{$search}%")
                    ->orWhere('created_by_name_snapshot', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhereHas('fundSource', function (Builder $fundQuery) use ($search) {
                        $fundQuery->withTrashed()
                            ->where(function (Builder $fundSearch) use ($search) {
                                $fundSearch->where('code', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
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
            ->orderByDesc('air_date')
            ->orderByDesc('created_at')
            ->paginate(
                perPage: max(1, min($size, 100)),
                columns: ['*'],
                pageName: 'page',
                page: max(1, $page),
            );
    }

    public function create(array $data): Air
    {
        return Air::query()->create($data)->load($this->relations());
    }

    public function save(Air $air): Air
    {
        $air->save();

        return $air->refresh()->load($this->relations());
    }

    public function delete(Air $air): void
    {
        $air->delete();
    }

    public function restore(Air $air): void
    {
        $air->restore();
    }

    public function forceDelete(Air $air): void
    {
        $air->forceDelete();
    }

    private function relations(): array
    {
        return [
            'department' => fn ($departmentQuery) => $departmentQuery
                ->withTrashed()
                ->select(['id', 'code', 'name']),
            'fundSource' => fn ($fundQuery) => $fundQuery
                ->withTrashed()
                ->select(['id', 'code', 'name']),
            'creator' => fn ($userQuery) => $userQuery
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
