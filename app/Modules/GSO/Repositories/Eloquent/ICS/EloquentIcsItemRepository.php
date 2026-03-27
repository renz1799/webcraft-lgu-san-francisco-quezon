<?php

namespace App\Modules\GSO\Repositories\Eloquent\ICS;

use App\Core\Models\Department;
use App\Modules\GSO\Models\IcsItem;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Repositories\Contracts\ICS\IcsItemRepositoryInterface;
use App\Modules\GSO\Support\InventoryCustodyStates;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EloquentIcsItemRepository implements IcsItemRepositoryInterface
{
    public function suggestFromGsoPool(string $icsId, string $q, int $limit = 10): array
    {
        $q = trim((string) $q);
        if ($q === '' || mb_strlen($q) < 2) {
            return [];
        }

        $gsoDeptId = $this->getGsoPoolDepartmentId();
        $icsClusterId = $this->resolveIcsFundClusterId($icsId);

        return DB::table('inventory_items as ii')
            ->leftJoin('items as it', 'it.id', '=', 'ii.item_id')
            ->leftJoin('fund_sources as fs', 'fs.id', '=', 'ii.fund_source_id')
            ->leftJoin('fund_clusters as fc', 'fc.id', '=', 'fs.fund_cluster_id')
            ->whereNull('ii.deleted_at')
            ->where('ii.is_ics', '=', 1)
            ->where('ii.department_id', '=', $gsoDeptId)
            ->where('ii.custody_state', '=', InventoryCustodyStates::POOL)
            ->where('fs.fund_cluster_id', '=', $icsClusterId)
            ->whereNotExists(function ($query) use ($icsId) {
                $query->select(DB::raw(1))
                    ->from('ics_items as ii2')
                    ->whereColumn('ii2.inventory_item_id', 'ii.id')
                    ->where('ii2.ics_id', '=', $icsId)
                    ->whereNull('ii2.deleted_at');
            })
            ->where(function ($query) use ($q) {
                $query->where('ii.property_number', 'like', "%{$q}%")
                    ->orWhere('ii.stock_number', 'like', "%{$q}%")
                    ->orWhere('ii.description', 'like', "%{$q}%")
                    ->orWhere('it.item_name', 'like', "%{$q}%");
            })
            ->orderBy('ii.property_number')
            ->limit($limit)
            ->get([
                'ii.id as inventory_item_id',
                'ii.property_number',
                'ii.stock_number',
                'ii.description',
                'ii.quantity',
                'ii.unit',
                'ii.acquisition_cost',
                'ii.service_life',
                DB::raw("COALESCE(it.item_name, '') as item_name"),
                'fs.code as fund_source_code',
                'fs.name as fund_source_name',
                'fc.code as fund_cluster_code',
                'fc.name as fund_cluster_name',
            ])
            ->map(function (object $row): array {
                $fundSourceCode = trim((string) ($row->fund_source_code ?? ''));
                $fundSourceName = trim((string) ($row->fund_source_name ?? ''));
                $fundClusterCode = trim((string) ($row->fund_cluster_code ?? ''));
                $fundClusterName = trim((string) ($row->fund_cluster_name ?? ''));

                return [
                    'inventory_item_id' => (string) ($row->inventory_item_id ?? ''),
                    'inventory_item_no' => (string) (($row->property_number ?: $row->stock_number) ?? ''),
                    'item_name' => (string) ($row->item_name ?? ''),
                    'description' => (string) ($row->description ?? ''),
                    'quantity' => max(1, (int) ($row->quantity ?? 1)),
                    'unit' => (string) ($row->unit ?? ''),
                    'unit_cost' => is_null($row->acquisition_cost) ? null : (float) $row->acquisition_cost,
                    'estimated_useful_life' => $row->service_life !== null ? (string) $row->service_life : null,
                    'fund_source_label' => trim(($fundSourceCode !== '' ? $fundSourceCode . ' - ' : '') . $fundSourceName),
                    'fund_cluster_label' => trim(($fundClusterCode !== '' ? $fundClusterCode . ' - ' : '') . $fundClusterName),
                ];
            })
            ->values()
            ->all();
    }

    public function addInventoryItemToIcs(string $icsId, string $inventoryItemId): IcsItem
    {
        $gsoDeptId = $this->getGsoPoolDepartmentId();
        $icsClusterId = $this->resolveIcsFundClusterId($icsId);

        $inventoryItem = InventoryItem::query()
            ->with(['item', 'fundSource.fundCluster'])
            ->whereKey($inventoryItemId)
            ->whereNull('deleted_at')
            ->firstOrFail();

        abort_if((bool) $inventoryItem->is_ics !== true, 409, 'Only ICS-classified inventory items can be added.');
        abort_if((string) ($inventoryItem->department_id ?? '') !== $gsoDeptId, 409, 'Item is not in the GSO pool.');
        abort_if((string) ($inventoryItem->custody_state ?? '') !== InventoryCustodyStates::POOL, 409, 'Item is not in the GSO pool.');

        $itemClusterId = trim((string) ($inventoryItem->fundSource?->fund_cluster_id ?? ''));
        abort_if($itemClusterId === '' || $itemClusterId !== $icsClusterId, 409, 'Item fund cluster does not match this ICS.');

        $exists = IcsItem::query()
            ->where('ics_id', $icsId)
            ->where('inventory_item_id', $inventoryItemId)
            ->whereNull('deleted_at')
            ->exists();
        abort_if($exists, 409, 'Item already added to this ICS.');

        $nextLine = ((int) IcsItem::query()->where('ics_id', $icsId)->max('line_no')) + 1;
        $quantity = max(1, (int) ($inventoryItem->quantity ?? 1));
        $unitCost = $inventoryItem->acquisition_cost !== null ? (float) $inventoryItem->acquisition_cost : null;
        $itemName = trim((string) ($inventoryItem->item?->item_name ?? ''));
        $description = trim((string) ($inventoryItem->description ?? ''));
        $inventoryNo = trim((string) ($inventoryItem->property_number ?: $inventoryItem->stock_number ?: ''));

        return IcsItem::query()->create([
            'ics_id' => $icsId,
            'inventory_item_id' => $inventoryItemId,
            'line_no' => $nextLine,
            'quantity' => $quantity,
            'unit_snapshot' => $inventoryItem->unit,
            'unit_cost_snapshot' => $unitCost,
            'total_cost_snapshot' => $unitCost !== null ? round($unitCost * $quantity, 2) : null,
            'description_snapshot' => $description !== '' ? $description : ($itemName !== '' ? $itemName : null),
            'inventory_item_no_snapshot' => $inventoryNo !== '' ? $inventoryNo : null,
            'estimated_useful_life_snapshot' => $inventoryItem->service_life !== null ? (string) $inventoryItem->service_life : null,
            'item_name_snapshot' => $itemName !== '' ? $itemName : null,
        ]);
    }

    public function listByIcsId(string $icsId): Collection
    {
        return IcsItem::query()
            ->where('ics_id', $icsId)
            ->whereNull('deleted_at')
            ->with(['inventoryItem', 'inventoryItem.item'])
            ->orderBy('line_no')
            ->orderBy('created_at')
            ->get();
    }

    public function findById(string $id): ?IcsItem
    {
        return IcsItem::query()->find($id);
    }

    public function delete(IcsItem $icsItem): void
    {
        $icsItem->delete();
    }

    private function getGsoPoolDepartmentId(): string
    {
        $configuredId = trim((string) config('gso.pool.department_id', ''));
        if ($configuredId !== '') {
            $department = Department::query()->find($configuredId);
            if ($department && ! $department->trashed() && (bool) ($department->is_active ?? true)) {
                return (string) $department->id;
            }
        }

        $configuredCode = trim((string) config('gso.pool.department_code', 'GSO'));
        if ($configuredCode !== '') {
            $department = Department::query()
                ->where('is_active', true)
                ->whereRaw('LOWER(code) = ?', [Str::lower($configuredCode)])
                ->first();

            if ($department) {
                return (string) $department->id;
            }
        }

        abort(500, 'GSO pool department is not configured.');
    }

    private function resolveIcsFundClusterId(string $icsId): string
    {
        $row = DB::table('ics as i')
            ->leftJoin('fund_sources as fs', 'fs.id', '=', 'i.fund_source_id')
            ->where('i.id', $icsId)
            ->whereNull('i.deleted_at')
            ->first([
                'i.fund_source_id',
                'fs.fund_cluster_id',
            ]);

        abort_if(! $row, 404, 'ICS not found.');

        $fundSourceId = trim((string) ($row->fund_source_id ?? ''));
        abort_if($fundSourceId === '', 422, 'Save a Fund Source first before managing ICS items.');

        $fundClusterId = trim((string) ($row->fund_cluster_id ?? ''));
        abort_if($fundClusterId === '', 422, 'The selected Fund Source has no Fund Cluster.');

        return $fundClusterId;
    }
}
