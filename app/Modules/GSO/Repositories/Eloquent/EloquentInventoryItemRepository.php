<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Repositories\Contracts\InventoryItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentInventoryItemRepository implements InventoryItemRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): InventoryItem
    {
        $query = InventoryItem::query()
            ->with($this->relations())
            ->withCount(['files', 'events']);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator
    {
        $query = InventoryItem::query()
            ->with($this->relations())
            ->withCount(['files', 'events']);

        $archived = $this->resolveArchivedMode($filters);
        $search = trim((string) ($filters['search'] ?? ''));
        $departmentId = trim((string) ($filters['department_id'] ?? ''));
        $itemId = trim((string) ($filters['item_id'] ?? ''));
        $fundSourceId = trim((string) ($filters['fund_source_id'] ?? ''));
        $classification = trim((string) ($filters['classification'] ?? ''));
        $custodyState = trim((string) ($filters['custody_state'] ?? ''));
        $inventoryStatus = trim((string) ($filters['inventory_status'] ?? ''));
        $condition = trim((string) ($filters['condition'] ?? ''));

        if ($archived === 'all') {
            $query->withTrashed();
        } elseif ($archived === 'archived') {
            $query->onlyTrashed();
        }

        if ($departmentId !== '') {
            $query->where('department_id', $departmentId);
        }

        if ($itemId !== '') {
            $query->where('item_id', $itemId);
        }

        if ($fundSourceId !== '') {
            $query->where('fund_source_id', $fundSourceId);
        }

        if ($classification === 'ics') {
            $query->where('is_ics', true);
        } elseif ($classification === 'ppe') {
            $query->where('is_ics', false);
        }

        if ($custodyState !== '') {
            $query->where('custody_state', $custodyState);
        }

        if ($inventoryStatus !== '') {
            $query->where('status', $inventoryStatus);
        }

        if ($condition !== '') {
            $query->where('condition', $condition);
        }

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('property_number', 'like', "%{$search}%")
                    ->orWhere('po_number', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('stock_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhere('accountable_officer', 'like', "%{$search}%")
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
                    })
                    ->orWhereHas('fundSource', function (Builder $fundSourceQuery) use ($search) {
                        $fundSourceQuery->withTrashed()
                            ->where(function (Builder $fundSourceSearch) use ($search) {
                                $fundSourceSearch->where('code', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('accountableOfficerRelation', function (Builder $officerQuery) use ($search) {
                        $officerQuery->withTrashed()
                            ->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query
            ->orderByDesc('acquisition_date')
            ->orderByDesc('created_at')
            ->paginate(
                perPage: max(1, min($size, 100)),
                columns: ['*'],
                pageName: 'page',
                page: max(1, $page),
            );
    }

    public function create(array $data): InventoryItem
    {
        return InventoryItem::query()->create($data)->load($this->relations());
    }

    public function save(InventoryItem $inventoryItem): InventoryItem
    {
        $inventoryItem->save();

        return $inventoryItem->refresh()->load($this->relations());
    }

    public function delete(InventoryItem $inventoryItem): void
    {
        $inventoryItem->delete();
    }

    public function restore(InventoryItem $inventoryItem): void
    {
        $inventoryItem->restore();
    }

    private function relations(): array
    {
        return [
            'item' => fn ($itemQuery) => $itemQuery
                ->withTrashed()
                ->select(['id', 'item_name', 'item_identification', 'tracking_type', 'requires_serial']),
            'department' => fn ($departmentQuery) => $departmentQuery
                ->withTrashed()
                ->select(['id', 'code', 'name']),
            'fundSource' => fn ($fundSourceQuery) => $fundSourceQuery
                ->withTrashed()
                ->select(['id', 'code', 'name']),
            'accountableOfficerRelation' => fn ($officerQuery) => $officerQuery
                ->withTrashed()
                ->select(['id', 'full_name', 'department_id']),
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
