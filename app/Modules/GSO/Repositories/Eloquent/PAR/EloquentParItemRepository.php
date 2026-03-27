<?php

namespace App\Modules\GSO\Repositories\Eloquent\PAR;

use App\Core\Models\Department;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Models\ParItem;
use App\Modules\GSO\Repositories\Contracts\PAR\ParItemRepositoryInterface;
use App\Modules\GSO\Support\InventoryCustodyStates;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EloquentParItemRepository implements ParItemRepositoryInterface
{
    public function add(array $data): ParItem
    {
        return ParItem::query()->create($data);
    }

    public function remove(string $parId, string $inventoryItemId): void
    {
        ParItem::query()
            ->where('par_id', $parId)
            ->where('inventory_item_id', $inventoryItemId)
            ->delete();
    }

    public function delete(ParItem $parItem): void
    {
        $parItem->delete();
    }

    public function listByParId(string $parId): Collection
    {
        return ParItem::query()
            ->where('par_id', $parId)
            ->whereNull('deleted_at')
            ->with(['inventoryItem', 'inventoryItem.item'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function exists(string $parId, string $inventoryItemId): bool
    {
        return ParItem::query()
            ->where('par_id', $parId)
            ->where('inventory_item_id', $inventoryItemId)
            ->whereNull('deleted_at')
            ->exists();
    }

    public function suggestFromGsoPool(string $parId, string $q, int $limit = 10): array
    {
        $q = trim((string) $q);
        if ($q === '' || mb_strlen($q) < 2) {
            return [];
        }

        $gsoDeptId = $this->getGsoPoolDepartmentId();
        $parClusterId = $this->resolveParFundClusterId($parId);

        return DB::table('inventory_items as ii')
            ->leftJoin('items as it', 'it.id', '=', 'ii.item_id')
            ->leftJoin('fund_sources as fs', 'fs.id', '=', 'ii.fund_source_id')
            ->leftJoin('fund_clusters as fc', 'fc.id', '=', 'fs.fund_cluster_id')
            ->whereNull('ii.deleted_at')
            ->where(function ($query) {
                $query->whereNull('ii.is_ics')
                    ->orWhere('ii.is_ics', false);
            })
            ->where('ii.department_id', '=', $gsoDeptId)
            ->where('ii.custody_state', '=', InventoryCustodyStates::POOL)
            ->where('fs.fund_cluster_id', '=', $parClusterId)
            ->whereNotExists(function ($query) use ($parId) {
                $query->select(DB::raw(1))
                    ->from('par_items as pi')
                    ->whereColumn('pi.inventory_item_id', 'ii.id')
                    ->where('pi.par_id', '=', $parId)
                    ->whereNull('pi.deleted_at');
            })
            ->where(function ($query) use ($q) {
                $query->where('ii.property_number', 'like', "%{$q}%")
                    ->orWhere('it.item_name', 'like', "%{$q}%");
            })
            ->orderBy('ii.property_number')
            ->limit($limit)
            ->get([
                'ii.id as inventory_item_id',
                'ii.property_number',
                'ii.unit',
                'ii.acquisition_cost',
                'ii.description',
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
                    'property_number' => (string) ($row->property_number ?? ''),
                    'item_name' => (string) ($row->item_name ?? ''),
                    'description' => (string) ($row->description ?? ''),
                    'unit' => (string) ($row->unit ?? ''),
                    'quantity' => 1,
                    'unit_cost' => is_null($row->acquisition_cost) ? null : (float) $row->acquisition_cost,
                    'total_cost' => is_null($row->acquisition_cost) ? null : (float) $row->acquisition_cost,
                    'amount' => is_null($row->acquisition_cost) ? null : (float) $row->acquisition_cost,
                    'fund_source_label' => $fundSourceCode !== '' ? $fundSourceCode . ' - ' . $fundSourceName : $fundSourceName,
                    'fund_cluster_label' => $fundClusterCode !== '' ? $fundClusterCode . ' - ' . $fundClusterName : $fundClusterName,
                ];
            })
            ->values()
            ->all();
    }

    public function addInventoryItemToPar(string $parId, string $inventoryItemId, int $quantity = 1): ParItem
    {
        $gsoDeptId = $this->getGsoPoolDepartmentId();

        $par = Par::query()
            ->with('fundSource')
            ->whereKey($parId)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $parFundSourceId = trim((string) ($par->fund_source_id ?? ''));
        abort_if($parFundSourceId === '', 422, 'Save a Fund Source first before adding PAR items.');

        $parClusterId = trim((string) ($par->fundSource?->fund_cluster_id ?? ''));
        abort_if($parClusterId === '', 422, 'The selected Fund Source has no Fund Cluster.');

        $inventoryItem = InventoryItem::query()
            ->with(['fundSource', 'item'])
            ->whereKey($inventoryItemId)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $itemFundSourceId = trim((string) ($inventoryItem->fund_source_id ?? ''));
        abort_if($itemFundSourceId === '', 422, 'This item has no Fund Source and cannot be added to a PAR.');

        $itemClusterId = trim((string) ($inventoryItem->fundSource?->fund_cluster_id ?? ''));
        abort_if($itemClusterId === '', 422, 'This item fund source has no Fund Cluster.');
        abort_if($itemClusterId !== $parClusterId, 422, "Fund Cluster mismatch. Only items under this PAR's Fund Cluster can be added.");

        abort_if((string) ($inventoryItem->department_id ?? '') !== $gsoDeptId, 409, 'Item is not in the GSO pool.');
        abort_if((string) ($inventoryItem->custody_state ?? '') !== InventoryCustodyStates::POOL, 409, 'Item is not in the GSO pool.');

        $exists = ParItem::query()
            ->where('par_id', $parId)
            ->where('inventory_item_id', $inventoryItemId)
            ->whereNull('deleted_at')
            ->exists();
        abort_if($exists, 409, 'Item already added to this PAR.');

        return ParItem::query()->create([
            'par_id' => $parId,
            'inventory_item_id' => $inventoryItemId,
            'quantity' => max(1, (int) $quantity),
            'property_number_snapshot' => $inventoryItem->property_number,
            'item_name_snapshot' => $inventoryItem->item?->item_name,
            'unit_snapshot' => $inventoryItem->unit,
            'amount_snapshot' => $inventoryItem->acquisition_cost,
        ]);
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

    private function resolveParFundClusterId(string $parId): string
    {
        $row = DB::table('pars as p')
            ->leftJoin('fund_sources as fs', 'fs.id', '=', 'p.fund_source_id')
            ->where('p.id', $parId)
            ->whereNull('p.deleted_at')
            ->first([
                'p.fund_source_id',
                'fs.fund_cluster_id',
            ]);

        abort_if(! $row, 404, 'PAR not found.');

        $fundSourceId = trim((string) ($row->fund_source_id ?? ''));
        abort_if($fundSourceId === '', 422, 'Save a Fund Source first before managing PAR items.');

        $fundClusterId = trim((string) ($row->fund_cluster_id ?? ''));
        abort_if($fundClusterId === '', 422, 'The selected Fund Source has no Fund Cluster.');

        return $fundClusterId;
    }
}
