<?php

namespace App\Modules\GSO\Repositories\Eloquent\PTR;

use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\PtrItem;
use App\Modules\GSO\Repositories\Contracts\PTR\PtrItemRepositoryInterface;
use App\Modules\GSO\Support\InventoryCustodyStates;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentPtrItemRepository implements PtrItemRepositoryInterface
{
    public function suggestTransferableItems(string $ptrId, string $q, int $limit = 10): array
    {
        $q = trim((string) $q);
        if ($q === '' || mb_strlen($q) < 2) {
            return [];
        }

        [$fromDepartmentId, $fromClusterId, $fromOfficer] = $this->resolvePtrSourceContext($ptrId);

        return DB::table('inventory_items as ii')
            ->leftJoin('items as it', 'it.id', '=', 'ii.item_id')
            ->leftJoin('departments as d', 'd.id', '=', 'ii.department_id')
            ->leftJoin('fund_sources as fs', 'fs.id', '=', 'ii.fund_source_id')
            ->leftJoin('fund_clusters as fc', 'fc.id', '=', 'fs.fund_cluster_id')
            ->whereNull('ii.deleted_at')
            ->where(function ($query) {
                $query->whereNull('ii.is_ics')
                    ->orWhere('ii.is_ics', '=', 0);
            })
            ->where('ii.custody_state', '=', InventoryCustodyStates::ISSUED)
            ->where('ii.department_id', '=', $fromDepartmentId)
            ->where('fs.fund_cluster_id', '=', $fromClusterId)
            ->whereRaw("TRIM(COALESCE(ii.accountable_officer, '')) <> ''")
            ->when($fromOfficer !== null, function ($query) use ($fromOfficer) {
                $query->whereRaw('LOWER(TRIM(ii.accountable_officer)) = ?', [$fromOfficer]);
            })
            ->whereNotExists(function ($query) use ($ptrId) {
                $query->select(DB::raw(1))
                    ->from('ptr_items as pi')
                    ->whereColumn('pi.inventory_item_id', 'ii.id')
                    ->where('pi.ptr_id', '=', $ptrId)
                    ->whereNull('pi.deleted_at');
            })
            ->whereNotExists(function ($query) use ($ptrId) {
                $query->select(DB::raw(1))
                    ->from('ptr_items as pi2')
                    ->join('ptrs as p2', 'p2.id', '=', 'pi2.ptr_id')
                    ->whereColumn('pi2.inventory_item_id', 'ii.id')
                    ->where('pi2.ptr_id', '!=', $ptrId)
                    ->whereNull('pi2.deleted_at')
                    ->whereNull('p2.deleted_at')
                    ->whereIn(DB::raw('LOWER(p2.status)'), ['draft', 'submitted']);
            })
            ->where(function ($query) use ($q) {
                $query->where('ii.property_number', 'like', "%{$q}%")
                    ->orWhere('ii.stock_number', 'like', "%{$q}%")
                    ->orWhere('ii.description', 'like', "%{$q}%")
                    ->orWhere('it.item_name', 'like', "%{$q}%");
            })
            ->orderBy('ii.property_number')
            ->orderBy('it.item_name')
            ->limit($limit)
            ->get([
                'ii.id as inventory_item_id',
                'ii.property_number',
                'ii.stock_number',
                'ii.acquisition_date',
                'ii.acquisition_cost',
                'ii.description',
                'ii.condition',
                'ii.accountable_officer',
                DB::raw("COALESCE(it.item_name, '') as item_name"),
                'd.code as department_code',
                'd.name as department_name',
                'fs.code as fund_source_code',
                'fs.name as fund_source_name',
                'fc.code as fund_cluster_code',
            ])
            ->map(function ($row) {
                $propertyNo = trim((string) ($row->property_number ?: $row->stock_number ?: ''));
                $departmentLabel = trim(
                    ((string) ($row->department_code ?? '')) .
                    (((string) ($row->department_code ?? '')) !== '' ? ' - ' : '') .
                    ((string) ($row->department_name ?? ''))
                );
                $fundSourceLabel = trim(
                    ((string) ($row->fund_source_code ?? '')) .
                    (((string) ($row->fund_source_code ?? '')) !== '' ? ' - ' : '') .
                    ((string) ($row->fund_source_name ?? ''))
                );

                return [
                    'inventory_item_id' => (string) ($row->inventory_item_id ?? ''),
                    'property_number' => $propertyNo,
                    'item_name' => (string) ($row->item_name ?? ''),
                    'description' => (string) ($row->description ?? ''),
                    'date_acquired' => $row->acquisition_date ? (string) $row->acquisition_date : null,
                    'amount' => is_null($row->acquisition_cost) ? null : (float) $row->acquisition_cost,
                    'condition' => (string) ($row->condition ?? ''),
                    'accountable_officer' => (string) ($row->accountable_officer ?? ''),
                    'department_label' => $departmentLabel,
                    'fund_source_label' => $fundSourceLabel,
                    'fund_cluster_code' => (string) ($row->fund_cluster_code ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    public function addInventoryItemToPtr(string $ptrId, string $inventoryItemId): PtrItem
    {
        [$fromDepartmentId, $fromClusterId, $fromOfficer] = $this->resolvePtrSourceContext($ptrId);

        $inventoryItem = InventoryItem::query()
            ->with(['item', 'fundSource.fundCluster'])
            ->whereKey($inventoryItemId)
            ->firstOrFail();

        abort_if((bool) $inventoryItem->is_ics === true, 409, 'Only PPE/property inventory items can be added to a PTR.');
        abort_if((string) ($inventoryItem->custody_state ?? '') !== InventoryCustodyStates::ISSUED, 409, 'Item is still in the GSO pool.');
        abort_if((string) $inventoryItem->department_id !== $fromDepartmentId, 409, 'Item is not currently assigned to the selected source department.');

        $itemOfficer = trim((string) ($inventoryItem->accountable_officer ?? ''));
        abort_if($itemOfficer === '', 409, 'Item has no accountable officer and cannot be transferred through PTR.');
        abort_if($fromOfficer !== null && mb_strtolower($itemOfficer) !== $fromOfficer, 409, 'Item accountable officer does not match the PTR source officer.');

        $itemClusterId = trim((string) optional($inventoryItem->fundSource)->fund_cluster_id);
        abort_if($itemClusterId === '' || $itemClusterId !== $fromClusterId, 409, 'Item fund cluster does not match this PTR source.');

        $exists = PtrItem::query()
            ->where('ptr_id', $ptrId)
            ->where('inventory_item_id', $inventoryItemId)
            ->whereNull('deleted_at')
            ->exists();
        abort_if($exists, 409, 'Item already added to this PTR.');

        $conflict = PtrItem::query()
            ->join('ptrs as p2', 'p2.id', '=', 'ptr_items.ptr_id')
            ->where('ptr_items.inventory_item_id', $inventoryItemId)
            ->where('ptr_items.ptr_id', '!=', $ptrId)
            ->whereNull('ptr_items.deleted_at')
            ->whereNull('p2.deleted_at')
            ->whereIn(DB::raw('LOWER(p2.status)'), ['draft', 'submitted'])
            ->exists();
        abort_if($conflict, 409, 'Item is already attached to another active PTR.');

        $nextLine = ((int) PtrItem::query()->where('ptr_id', $ptrId)->max('line_no')) + 1;
        $itemName = trim((string) ($inventoryItem->item?->item_name ?? ''));
        $description = trim((string) ($inventoryItem->description ?? ''));
        $propertyNo = trim((string) ($inventoryItem->property_number ?: $inventoryItem->stock_number ?: ''));

        return PtrItem::query()->create([
            'ptr_id' => $ptrId,
            'inventory_item_id' => $inventoryItemId,
            'line_no' => $nextLine,
            'date_acquired_snapshot' => $inventoryItem->acquisition_date,
            'property_number_snapshot' => $propertyNo !== '' ? $propertyNo : null,
            'description_snapshot' => $description !== '' ? $description : ($itemName !== '' ? $itemName : null),
            'amount_snapshot' => $inventoryItem->acquisition_cost,
            'condition_snapshot' => $inventoryItem->condition,
            'item_name_snapshot' => $itemName !== '' ? $itemName : null,
        ]);
    }

    public function listByPtrId(string $ptrId): Collection
    {
        return PtrItem::query()
            ->where('ptr_id', $ptrId)
            ->with(['inventoryItem', 'inventoryItem.item'])
            ->orderBy('line_no')
            ->orderBy('created_at')
            ->get();
    }

    public function findById(string $id): ?PtrItem
    {
        return PtrItem::query()->find($id);
    }

    public function delete(PtrItem $ptrItem): void
    {
        $ptrItem->delete();
    }

    private function resolvePtrSourceContext(string $ptrId): array
    {
        $row = DB::table('ptrs as p')
            ->leftJoin('fund_sources as fs', 'fs.id', '=', 'p.from_fund_source_id')
            ->where('p.id', $ptrId)
            ->whereNull('p.deleted_at')
            ->first([
                'p.from_department_id',
                'p.from_accountable_officer',
                'p.from_fund_source_id',
                'fs.fund_cluster_id',
            ]);

        abort_if(!$row, 404, 'PTR not found.');

        $fromDepartmentId = trim((string) ($row->from_department_id ?? ''));
        abort_if($fromDepartmentId === '', 422, 'Save the source department first before managing PTR items.');

        $fromFundSourceId = trim((string) ($row->from_fund_source_id ?? ''));
        abort_if($fromFundSourceId === '', 422, 'Save the source fund source first before managing PTR items.');

        $fromClusterId = trim((string) ($row->fund_cluster_id ?? ''));
        abort_if($fromClusterId === '', 422, 'The selected source fund source has no Fund Cluster.');

        $fromOfficer = trim((string) ($row->from_accountable_officer ?? ''));

        return [
            $fromDepartmentId,
            $fromClusterId,
            $fromOfficer !== '' ? mb_strtolower($fromOfficer) : null,
        ];
    }
}
