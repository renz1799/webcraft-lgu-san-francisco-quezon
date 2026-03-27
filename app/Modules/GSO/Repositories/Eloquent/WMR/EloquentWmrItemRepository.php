<?php

namespace App\Modules\GSO\Repositories\Eloquent\WMR;

use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\WmrItem;
use App\Modules\GSO\Repositories\Contracts\WMR\WmrItemRepositoryInterface;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentWmrItemRepository implements WmrItemRepositoryInterface
{
    public function suggestDisposableItems(string $wmrId, string $q, int $limit = 10): array
    {
        $q = trim((string) $q);
        if ($q === '' || mb_strlen($q) < 2) {
            return [];
        }

        $fundClusterId = $this->resolveWmrFundClusterId($wmrId);
        $eligibleStatuses = array_map('strtolower', [
            InventoryStatuses::SERVICEABLE,
            InventoryStatuses::FOR_REPAIR,
            InventoryStatuses::UNDER_REPAIR,
            InventoryStatuses::UNSERVICEABLE,
        ]);

        return DB::table('inventory_items as ii')
            ->leftJoin('items as it', 'it.id', '=', 'ii.item_id')
            ->leftJoin('departments as d', 'd.id', '=', 'ii.department_id')
            ->leftJoin('fund_sources as fs', 'fs.id', '=', 'ii.fund_source_id')
            ->leftJoin('fund_clusters as fc', 'fc.id', '=', 'fs.fund_cluster_id')
            ->whereNull('ii.deleted_at')
            ->where(function ($query) use ($eligibleStatuses) {
                $query->whereNull('ii.status')
                    ->orWhereIn(DB::raw('LOWER(ii.status)'), $eligibleStatuses);
            })
            ->where('fs.fund_cluster_id', '=', $fundClusterId)
            ->whereNotExists(function ($query) use ($wmrId) {
                $query->select(DB::raw(1))
                    ->from('wmr_items as wi2')
                    ->whereColumn('wi2.inventory_item_id', 'ii.id')
                    ->where('wi2.wmr_id', '=', $wmrId)
                    ->whereNull('wi2.deleted_at');
            })
            ->whereNotExists(function ($query) use ($wmrId) {
                $query->select(DB::raw(1))
                    ->from('wmr_items as wi3')
                    ->join('wmrs as w3', 'w3.id', '=', 'wi3.wmr_id')
                    ->whereColumn('wi3.inventory_item_id', 'ii.id')
                    ->where('wi3.wmr_id', '!=', $wmrId)
                    ->whereNull('wi3.deleted_at')
                    ->whereNull('w3.deleted_at')
                    ->whereIn(DB::raw('LOWER(w3.status)'), ['draft', 'submitted', 'approved']);
            })
            ->where(function ($query) use ($q) {
                $query->where('ii.property_number', 'like', "%{$q}%")
                    ->orWhere('ii.stock_number', 'like', "%{$q}%")
                    ->orWhere('ii.description', 'like', "%{$q}%")
                    ->orWhere('ii.model', 'like', "%{$q}%")
                    ->orWhere('ii.serial_number', 'like', "%{$q}%")
                    ->orWhere('it.item_name', 'like', "%{$q}%");
            })
            ->orderBy('ii.property_number')
            ->orderBy('ii.stock_number')
            ->orderBy('it.item_name')
            ->limit($limit)
            ->get([
                'ii.id as inventory_item_id',
                'ii.property_number',
                'ii.stock_number',
                'ii.description',
                'ii.quantity',
                'ii.unit',
                'ii.condition',
                'ii.acquisition_date',
                'ii.acquisition_cost',
                'ii.status',
                'ii.accountable_officer',
                DB::raw("COALESCE(it.item_name, '') as item_name"),
                'd.code as department_code',
                'd.name as department_name',
                'fc.code as fund_cluster_code',
            ])
            ->map(function ($row) {
                $referenceNo = trim((string) ($row->property_number ?: $row->stock_number ?: ''));
                $departmentLabel = trim(
                    ((string) ($row->department_code ?? '')) .
                    (((string) ($row->department_code ?? '')) !== '' ? ' - ' : '') .
                    ((string) ($row->department_name ?? ''))
                );

                return [
                    'inventory_item_id' => (string) ($row->inventory_item_id ?? ''),
                    'reference_no' => $referenceNo,
                    'item_name' => (string) ($row->item_name ?? ''),
                    'description' => (string) ($row->description ?? ''),
                    'quantity' => max(1, (int) ($row->quantity ?? 1)),
                    'unit' => (string) ($row->unit ?? ''),
                    'condition' => (string) ($row->condition ?? ''),
                    'acquisition_date' => $row->acquisition_date ? (string) $row->acquisition_date : null,
                    'acquisition_cost' => is_null($row->acquisition_cost) ? null : (float) $row->acquisition_cost,
                    'status' => (string) ($row->status ?? ''),
                    'accountable_officer' => (string) ($row->accountable_officer ?? ''),
                    'department_label' => $departmentLabel,
                    'fund_cluster_code' => (string) ($row->fund_cluster_code ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    public function addInventoryItemToWmr(string $wmrId, string $inventoryItemId): WmrItem
    {
        $fundClusterId = $this->resolveWmrFundClusterId($wmrId);
        $eligibleStatuses = [
            InventoryStatuses::SERVICEABLE,
            InventoryStatuses::FOR_REPAIR,
            InventoryStatuses::UNDER_REPAIR,
            InventoryStatuses::UNSERVICEABLE,
        ];

        $inventoryItem = InventoryItem::query()
            ->with(['item', 'fundSource.fundCluster'])
            ->whereKey($inventoryItemId)
            ->firstOrFail();

        $status = trim((string) ($inventoryItem->status ?? ''));
        abort_if($status !== '' && !in_array(mb_strtolower($status), array_map('strtolower', $eligibleStatuses), true), 409, 'Item is not eligible for disposal through WMR.');

        $itemClusterId = trim((string) optional($inventoryItem->fundSource)->fund_cluster_id);
        abort_if($itemClusterId === '' || $itemClusterId !== $fundClusterId, 409, 'Item fund cluster does not match this WMR.');

        $exists = WmrItem::query()
            ->where('wmr_id', $wmrId)
            ->where('inventory_item_id', $inventoryItemId)
            ->whereNull('deleted_at')
            ->exists();
        abort_if($exists, 409, 'Item already added to this WMR.');

        $conflict = WmrItem::query()
            ->join('wmrs as w2', 'w2.id', '=', 'wmr_items.wmr_id')
            ->where('wmr_items.inventory_item_id', $inventoryItemId)
            ->where('wmr_items.wmr_id', '!=', $wmrId)
            ->whereNull('wmr_items.deleted_at')
            ->whereNull('w2.deleted_at')
            ->whereIn(DB::raw('LOWER(w2.status)'), ['draft', 'submitted', 'approved'])
            ->exists();
        abort_if($conflict, 409, 'Item is already attached to another active WMR.');

        $nextLine = ((int) WmrItem::query()->where('wmr_id', $wmrId)->max('line_no')) + 1;
        $itemName = trim((string) ($inventoryItem->item?->item_name ?? ''));
        $description = trim((string) ($inventoryItem->description ?? ''));
        $referenceNo = trim((string) ($inventoryItem->property_number ?: $inventoryItem->stock_number ?: ''));

        return WmrItem::query()->create([
            'wmr_id' => $wmrId,
            'inventory_item_id' => $inventoryItemId,
            'line_no' => $nextLine,
            'quantity' => max(1, (int) ($inventoryItem->quantity ?? 1)),
            'unit_snapshot' => $inventoryItem->unit,
            'description_snapshot' => $description !== '' ? $description : ($itemName !== '' ? $itemName : null),
            'item_name_snapshot' => $itemName !== '' ? $itemName : null,
            'reference_no_snapshot' => $referenceNo !== '' ? $referenceNo : null,
            'date_acquired_snapshot' => $inventoryItem->acquisition_date,
            'acquisition_cost_snapshot' => $inventoryItem->acquisition_cost,
            'condition_snapshot' => $inventoryItem->condition,
            'disposal_method' => null,
            'transfer_entity_name' => null,
            'official_receipt_no' => null,
            'official_receipt_date' => null,
            'official_receipt_amount' => null,
        ]);
    }

    public function updateLine(WmrItem $wmrItem, array $attributes): WmrItem
    {
        $wmrItem->fill($attributes);
        $wmrItem->save();

        return $wmrItem->refresh();
    }

    public function listByWmrId(string $wmrId): Collection
    {
        return WmrItem::query()
            ->where('wmr_id', $wmrId)
            ->with(['inventoryItem', 'inventoryItem.item'])
            ->orderBy('line_no')
            ->orderBy('created_at')
            ->get();
    }

    public function findById(string $id): ?WmrItem
    {
        return WmrItem::query()->find($id);
    }

    public function delete(WmrItem $wmrItem): void
    {
        $wmrItem->delete();
    }

    private function resolveWmrFundClusterId(string $wmrId): string
    {
        $row = DB::table('wmrs')
            ->where('id', $wmrId)
            ->whereNull('deleted_at')
            ->first(['fund_cluster_id']);

        abort_if(!$row, 404, 'WMR not found.');

        $fundClusterId = trim((string) ($row->fund_cluster_id ?? ''));
        abort_if($fundClusterId === '', 422, 'Save the WMR fund cluster first before managing disposal items.');

        return $fundClusterId;
    }
}

