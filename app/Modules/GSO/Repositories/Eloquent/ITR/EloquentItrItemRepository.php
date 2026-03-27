<?php

namespace App\Modules\GSO\Repositories\Eloquent\ITR;

use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\ItrItem;
use App\Modules\GSO\Repositories\Contracts\ITR\ItrItemRepositoryInterface;
use App\Modules\GSO\Support\InventoryCustodyStates;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentItrItemRepository implements ItrItemRepositoryInterface
{
    public function suggestTransferableItems(string $itrId, string $q, int $limit = 10): array
    {
        $q = trim((string) $q);
        if ($q === '' || mb_strlen($q) < 2) {
            return [];
        }

        [$fromDepartmentId, $fromClusterId, $fromOfficer] = $this->resolveItrSourceContext($itrId);

        return DB::table('inventory_items as ii')
            ->leftJoin('items as it', 'it.id', '=', 'ii.item_id')
            ->leftJoin('departments as d', 'd.id', '=', 'ii.department_id')
            ->leftJoin('fund_sources as fs', 'fs.id', '=', 'ii.fund_source_id')
            ->leftJoin('fund_clusters as fc', 'fc.id', '=', 'fs.fund_cluster_id')
            ->whereNull('ii.deleted_at')
            ->where('ii.is_ics', '=', 1)
            ->where('ii.custody_state', '=', InventoryCustodyStates::ISSUED)
            ->where('ii.department_id', '=', $fromDepartmentId)
            ->where('fs.fund_cluster_id', '=', $fromClusterId)
            ->whereRaw("TRIM(COALESCE(ii.accountable_officer, '')) <> ''")
            ->when($fromOfficer !== null, function ($query) use ($fromOfficer) {
                $query->whereRaw('LOWER(TRIM(ii.accountable_officer)) = ?', [$fromOfficer]);
            })
            ->whereNotExists(function ($query) use ($itrId) {
                $query->select(DB::raw(1))
                    ->from('itr_items as ii2')
                    ->whereColumn('ii2.inventory_item_id', 'ii.id')
                    ->where('ii2.itr_id', '=', $itrId)
                    ->whereNull('ii2.deleted_at');
            })
            ->whereNotExists(function ($query) use ($itrId) {
                $query->select(DB::raw(1))
                    ->from('itr_items as ii3')
                    ->join('itrs as i3', 'i3.id', '=', 'ii3.itr_id')
                    ->whereColumn('ii3.inventory_item_id', 'ii.id')
                    ->where('ii3.itr_id', '!=', $itrId)
                    ->whereNull('ii3.deleted_at')
                    ->whereNull('i3.deleted_at')
                    ->whereIn(DB::raw('LOWER(i3.status)'), ['draft', 'submitted']);
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
                'ii.quantity',
                'ii.service_life',
                'ii.accountable_officer',
                DB::raw("COALESCE(it.item_name, '') as item_name"),
                'd.code',
                'd.name',
                'fs.code as fund_source_code',
                'fs.name as fund_source_name',
                'fc.code as fund_cluster_code',
            ])
            ->map(function ($row) {
                $inventoryNo = trim((string) ($row->property_number ?: $row->stock_number ?: ''));
                $departmentLabel = trim(
                    ((string) ($row->code ?? '')) .
                    (((string) ($row->code ?? '')) !== '' ? ' - ' : '') .
                    ((string) ($row->name ?? ''))
                );
                $fundSourceLabel = trim(
                    ((string) ($row->fund_source_code ?? '')) .
                    (((string) ($row->fund_source_code ?? '')) !== '' ? ' - ' : '') .
                    ((string) ($row->fund_source_name ?? ''))
                );

                return [
                    'inventory_item_id' => (string) ($row->inventory_item_id ?? ''),
                    'inventory_item_no' => $inventoryNo,
                    'item_name' => (string) ($row->item_name ?? ''),
                    'description' => (string) ($row->description ?? ''),
                    'quantity' => (int) ($row->quantity ?? 1),
                    'date_acquired' => $row->acquisition_date ? (string) $row->acquisition_date : null,
                    'amount' => is_null($row->acquisition_cost) ? null : (float) $row->acquisition_cost,
                    'estimated_useful_life' => is_null($row->service_life) ? '' : (string) $row->service_life,
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

    public function addInventoryItemToItr(string $itrId, string $inventoryItemId): ItrItem
    {
        [$fromDepartmentId, $fromClusterId, $fromOfficer] = $this->resolveItrSourceContext($itrId);

        $inventoryItem = InventoryItem::query()
            ->with(['item', 'fundSource.fundCluster'])
            ->whereKey($inventoryItemId)
            ->firstOrFail();

        abort_if((bool) $inventoryItem->is_ics !== true, 409, 'Only issued ICS / semi-expendable inventory items can be added to an ITR.');
        abort_if((string) ($inventoryItem->custody_state ?? '') !== InventoryCustodyStates::ISSUED, 409, 'Item is still in the GSO pool.');
        abort_if((string) $inventoryItem->department_id !== $fromDepartmentId, 409, 'Item is not currently assigned to the selected source department.');

        $itemOfficer = trim((string) ($inventoryItem->accountable_officer ?? ''));
        abort_if($itemOfficer === '', 409, 'Item has no accountable officer and cannot be transferred through ITR.');
        abort_if($fromOfficer !== null && mb_strtolower($itemOfficer) !== $fromOfficer, 409, 'Item accountable officer does not match the ITR source officer.');

        $itemClusterId = trim((string) optional($inventoryItem->fundSource)->fund_cluster_id);
        abort_if($itemClusterId === '' || $itemClusterId !== $fromClusterId, 409, 'Item fund cluster does not match this ITR source.');

        $exists = ItrItem::query()
            ->where('itr_id', $itrId)
            ->where('inventory_item_id', $inventoryItemId)
            ->whereNull('deleted_at')
            ->exists();
        abort_if($exists, 409, 'Item already added to this ITR.');

        $conflict = ItrItem::query()
            ->join('itrs as i2', 'i2.id', '=', 'itr_items.itr_id')
            ->where('itr_items.inventory_item_id', $inventoryItemId)
            ->where('itr_items.itr_id', '!=', $itrId)
            ->whereNull('itr_items.deleted_at')
            ->whereNull('i2.deleted_at')
            ->whereIn(DB::raw('LOWER(i2.status)'), ['draft', 'submitted'])
            ->exists();
        abort_if($conflict, 409, 'Item is already attached to another active ITR.');

        $nextLine = ((int) ItrItem::query()->where('itr_id', $itrId)->max('line_no')) + 1;
        $itemName = trim((string) ($inventoryItem->item?->item_name ?? ''));
        $description = trim((string) ($inventoryItem->description ?? ''));
        $inventoryNo = trim((string) ($inventoryItem->property_number ?: $inventoryItem->stock_number ?: ''));
        $usefulLife = is_null($inventoryItem->service_life) ? null : (string) $inventoryItem->service_life;

        return ItrItem::query()->create([
            'itr_id' => $itrId,
            'inventory_item_id' => $inventoryItemId,
            'line_no' => $nextLine,
            'quantity' => max(1, (int) ($inventoryItem->quantity ?? 1)),
            'date_acquired_snapshot' => $inventoryItem->acquisition_date,
            'inventory_item_no_snapshot' => $inventoryNo !== '' ? $inventoryNo : null,
            'description_snapshot' => $description !== '' ? $description : ($itemName !== '' ? $itemName : null),
            'amount_snapshot' => $inventoryItem->acquisition_cost,
            'estimated_useful_life_snapshot' => $usefulLife,
            'condition_snapshot' => $inventoryItem->condition,
            'item_name_snapshot' => $itemName !== '' ? $itemName : null,
        ]);
    }

    public function listByItrId(string $itrId): Collection
    {
        return ItrItem::query()
            ->where('itr_id', $itrId)
            ->with(['inventoryItem', 'inventoryItem.item'])
            ->orderBy('line_no')
            ->orderBy('created_at')
            ->get();
    }

    public function findById(string $id): ?ItrItem
    {
        return ItrItem::query()->find($id);
    }

    public function delete(ItrItem $itrItem): void
    {
        $itrItem->delete();
    }

    private function resolveItrSourceContext(string $itrId): array
    {
        $row = DB::table('itrs as i')
            ->leftJoin('fund_sources as fs', 'fs.id', '=', 'i.from_fund_source_id')
            ->where('i.id', $itrId)
            ->whereNull('i.deleted_at')
            ->first([
                'i.from_department_id',
                'i.from_accountable_officer',
                'i.from_fund_source_id',
                'fs.fund_cluster_id',
            ]);

        abort_if(!$row, 404, 'ITR not found.');

        $fromDepartmentId = trim((string) ($row->from_department_id ?? ''));
        abort_if($fromDepartmentId === '', 422, 'Save the source department first before managing ITR items.');

        $fromFundSourceId = trim((string) ($row->from_fund_source_id ?? ''));
        abort_if($fromFundSourceId === '', 422, 'Save the source fund source first before managing ITR items.');

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



