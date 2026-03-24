<?php

namespace App\Modules\GSO\Data\RIS;

use App\Modules\GSO\Data\Contracts\RIS\RisItemDataProviderInterface;
use Illuminate\Support\Facades\DB;

class RisItemDataProvider implements RisItemDataProviderInterface
{
    public function getFundSourceContext(string $fundSourceId): ?array
    {
        $row = DB::table('fund_sources')
            ->where('id', $fundSourceId)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'code',
                'name',
                'fund_cluster_id',
            ]);

        if (!$row) {
            return null;
        }

        return [
            'id' => (string) $row->id,
            'code' => (string) ($row->code ?? ''),
            'name' => (string) ($row->name ?? ''),
            'fund_cluster_id' => (string) ($row->fund_cluster_id ?? ''),
        ];
    }

    public function getConsumableSuggestionRows(
        string $risFundSourceId,
        string $search = '',
        array $excludeItemIds = [],
    ): array {
        $fundSource = $this->getFundSourceContext($risFundSourceId);

        if (!$fundSource) {
            return [];
        }

        $clusterId = trim((string) ($fundSource['fund_cluster_id'] ?? ''));
        $search = trim($search);

        return DB::table('stocks as s')
            ->join('items as it', 'it.id', '=', 's.item_id')
            ->join('fund_sources as fs', 'fs.id', '=', 's.fund_source_id')
            ->whereNull('s.deleted_at')
            ->whereNull('it.deleted_at')
            ->whereNull('fs.deleted_at')
            ->whereRaw("LOWER(TRIM(COALESCE(it.tracking_type, ''))) IN ('consumable','consumables')")
            ->when(
                $clusterId !== '',
                fn ($query) => $query->where('fs.fund_cluster_id', '=', $clusterId),
                fn ($query) => $query->where('fs.id', '=', $risFundSourceId)
            )
            ->when(
                !empty($excludeItemIds),
                fn ($query) => $query->whereNotIn('it.id', $excludeItemIds)
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($where) use ($search) {
                    $where->where('it.item_name', 'like', "%{$search}%")
                        ->orWhere('it.description', 'like', "%{$search}%")
                        ->orWhere('it.item_identification', 'like', "%{$search}%");
                });
            })
            ->select([
                'it.id as item_id',
                'it.item_name',
                'it.item_identification',
                'it.description',
                'it.base_unit',
                's.fund_source_id',
                DB::raw('COALESCE(s.on_hand, 0) as on_hand'),
                DB::raw("COALESCE(fs.code, '') as fund_code"),
                DB::raw("COALESCE(fs.name, '') as fund_name"),
            ])
            ->orderBy('it.item_name')
            ->orderBy('fund_code')
            ->limit(30)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->values()
            ->all();
    }

    public function getEditRows(string $risId, string $fundSourceId): array
    {
        return DB::table('ris_items as ri')
            ->join('items as it', 'it.id', '=', 'ri.item_id')
            ->leftJoin('stocks as s', function ($join) use ($fundSourceId) {
                $join->on('s.item_id', '=', 'it.id')
                    ->whereNull('s.deleted_at')
                    ->where('s.fund_source_id', '=', $fundSourceId);
            })
            ->where('ri.ris_id', $risId)
            ->whereNull('ri.deleted_at')
            ->orderBy('ri.line_no')
            ->orderBy('it.item_name')
            ->select([
                'ri.id',
                'ri.item_id',
                'ri.stock_no_snapshot',
                'ri.description_snapshot',
                'ri.unit_snapshot',
                'ri.qty_requested',
                'ri.qty_issued',
                DB::raw("COALESCE(it.item_name,'') as item_name"),
                DB::raw("COALESCE(it.base_unit,'') as base_unit"),
                DB::raw("COALESCE(s.on_hand, 0) as on_hand_base"),
            ])
            ->get()
            ->map(fn ($row) => (array) $row)
            ->values()
            ->all();
    }

    public function getOnHandForItemAndFundSource(string $itemId, string $fundSourceId): int
    {
        return (int) (
            DB::table('stocks')
                ->where('item_id', $itemId)
                ->where('fund_source_id', $fundSourceId)
                ->whereNull('deleted_at')
                ->value('on_hand') ?? 0
        );
    }

    public function getItemSnapshot(string $itemId): ?array
    {
        $row = DB::table('items')
            ->where('id', $itemId)
            ->whereNull('deleted_at')
            ->select([
                'id',
                'item_name',
                'item_identification',
                'base_unit',
            ])
            ->first();

        if (!$row) {
            return null;
        }

        return [
            'id' => (string) $row->id,
            'item_name' => (string) ($row->item_name ?? ''),
            'item_identification' => (string) ($row->item_identification ?? ''),
            'base_unit' => (string) ($row->base_unit ?? ''),
        ];
    }
}