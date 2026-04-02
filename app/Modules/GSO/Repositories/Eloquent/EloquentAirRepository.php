<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Repositories\Contracts\AirRepositoryInterface;
use App\Modules\GSO\Support\Air\AirStatuses;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

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
        $query = Air::query()
            ->select('airs.*')
            ->with($this->relations())
            ->selectSub($this->propertyPromotableUnitsCountSubquery(), 'property_promotable_units_count')
            ->selectSub($this->propertyPendingUnitsCountSubquery(), 'property_pending_units_count')
            ->selectSub($this->consumablePromotableLinesCountSubquery(), 'consumable_promotable_lines_count')
            ->selectSub($this->consumablePendingLinesCountSubquery(), 'consumable_pending_lines_count');
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

        $this->applySorting($query, $filters);

        return $query->paginate(
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

    private function applySorting(Builder $query, array $filters): void
    {
        $sortField = trim((string) ($filters['sorters'][0]['field'] ?? ''));
        $sortDir = (($filters['sorters'][0]['dir'] ?? 'desc') === 'asc') ? 'asc' : 'desc';

        if ($sortField === 'promotion_status' || $sortField === 'promotion_status_text') {
            $this->applyPromotionStatusSorting($query, $sortDir);

            return;
        }

        $sortMap = [
            'air_number' => 'airs.air_number',
            'air_date_text' => 'airs.air_date',
            'air_date' => 'airs.air_date',
            'po_number' => 'airs.po_number',
            'supplier_name' => 'airs.supplier_name',
            'department_label' => 'airs.requesting_department_name_snapshot',
            'received_completeness' => 'airs.received_completeness',
            'status' => 'airs.status',
            'created_at_text' => 'airs.created_at',
            'created_at' => 'airs.created_at',
        ];

        $column = $sortMap[$sortField] ?? null;

        if ($column !== null) {
            $query->orderBy($column, $sortDir);
            $query->orderByDesc('airs.created_at');

            return;
        }

        $query->orderByDesc('airs.air_date');
        $query->orderByDesc('airs.created_at');
    }

    private function applyPromotionStatusSorting(Builder $query, string $sortDir): void
    {
        $query->orderByRaw(
            "CASE
                WHEN airs.status = ? AND ((COALESCE(property_pending_units_count, 0) + COALESCE(consumable_pending_lines_count, 0)) > 0) THEN 0
                WHEN airs.status = ? AND ((COALESCE(property_promotable_units_count, 0) + COALESCE(consumable_promotable_lines_count, 0)) > 0) THEN 1
                ELSE 2
            END {$sortDir}",
            [AirStatuses::INSPECTED, AirStatuses::INSPECTED]
        );

        if ($sortDir === 'asc') {
            $query->orderByRaw('(COALESCE(property_pending_units_count, 0) + COALESCE(consumable_pending_lines_count, 0)) DESC');
        } else {
            $query->orderByRaw('(COALESCE(property_pending_units_count, 0) + COALESCE(consumable_pending_lines_count, 0)) ASC');
        }

        $query->orderByDesc('airs.created_at');
    }

    private function propertyPromotableUnitsCountSubquery(): QueryBuilder
    {
        return DB::table('air_item_units')
            ->join('air_items', 'air_item_units.air_item_id', '=', 'air_items.id')
            ->selectRaw('COUNT(*)')
            ->whereColumn('air_items.air_id', 'airs.id')
            ->whereNull('air_item_units.deleted_at')
            ->where(function ($query) {
                $query->whereRaw("LOWER(COALESCE(air_items.tracking_type_snapshot, '')) <> 'consumable'")
                    ->orWhere('air_items.requires_serial_snapshot', true)
                    ->orWhere('air_items.is_semi_expendable_snapshot', true);
            });
    }

    private function propertyPendingUnitsCountSubquery(): QueryBuilder
    {
        return DB::table('air_item_units')
            ->join('air_items', 'air_item_units.air_item_id', '=', 'air_items.id')
            ->selectRaw('COUNT(*)')
            ->whereColumn('air_items.air_id', 'airs.id')
            ->whereNull('air_item_units.deleted_at')
            ->where(function ($query) {
                $query->whereRaw("LOWER(COALESCE(air_items.tracking_type_snapshot, '')) <> 'consumable'")
                    ->orWhere('air_items.requires_serial_snapshot', true)
                    ->orWhere('air_items.is_semi_expendable_snapshot', true);
            })
            ->where(function ($query) {
                $query->whereNull('air_item_units.inventory_item_id')
                    ->orWhere('air_item_units.inventory_item_id', '');
            })
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('inventory_items')
                    ->whereColumn('inventory_items.air_item_unit_id', 'air_item_units.id');
            });
    }

    private function consumablePromotableLinesCountSubquery(): QueryBuilder
    {
        return DB::table('air_items')
            ->selectRaw('COUNT(*)')
            ->whereColumn('air_items.air_id', 'airs.id')
            ->where('air_items.qty_accepted', '>', 0)
            ->whereRaw("LOWER(COALESCE(air_items.tracking_type_snapshot, '')) = 'consumable'")
            ->where(function ($query) {
                $query->whereNull('air_items.requires_serial_snapshot')
                    ->orWhere('air_items.requires_serial_snapshot', false);
            })
            ->where(function ($query) {
                $query->whereNull('air_items.is_semi_expendable_snapshot')
                    ->orWhere('air_items.is_semi_expendable_snapshot', false);
            });
    }

    private function consumablePendingLinesCountSubquery(): QueryBuilder
    {
        return DB::table('air_items')
            ->selectRaw('COUNT(*)')
            ->whereColumn('air_items.air_id', 'airs.id')
            ->where('air_items.qty_accepted', '>', 0)
            ->whereRaw("LOWER(COALESCE(air_items.tracking_type_snapshot, '')) = 'consumable'")
            ->where(function ($query) {
                $query->whereNull('air_items.requires_serial_snapshot')
                    ->orWhere('air_items.requires_serial_snapshot', false);
            })
            ->where(function ($query) {
                $query->whereNull('air_items.is_semi_expendable_snapshot')
                    ->orWhere('air_items.is_semi_expendable_snapshot', false);
            })
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('stock_movements')
                    ->whereColumn('stock_movements.air_item_id', 'air_items.id')
                    ->whereColumn('stock_movements.reference_id', 'airs.id')
                    ->where('stock_movements.reference_type', 'AIR');
            });
    }
}
