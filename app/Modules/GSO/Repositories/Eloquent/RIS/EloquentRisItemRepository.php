<?php

namespace App\Modules\GSO\Repositories\Eloquent\RIS;

use App\Modules\GSO\Models\RisItem;
use App\Modules\GSO\Repositories\Contracts\RIS\RisItemRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EloquentRisItemRepository implements RisItemRepositoryInterface
{
    public function listByRisId(string $risId): Collection
    {
        return RisItem::query()
            ->where('ris_id', $risId)
            ->orderBy('created_at')
            ->get();
    }

    public function findById(string $id): ?RisItem
    {
        return RisItem::query()->find($id);
    }

    public function findByRisIdAndItemId(string $risId, string $itemId): ?RisItem
    {
        return RisItem::query()
            ->where('ris_id', $risId)
            ->where('item_id', $itemId)
            ->first();
    }

    public function create(array $data): RisItem
    {
        return RisItem::query()->create($data);
    }

    public function update(RisItem $risItem, array $data): RisItem
    {
        $risItem->fill($data);
        $risItem->save();

        return $risItem->refresh();
    }

    public function forceDelete(RisItem $risItem): void
    {
        $risItem->forceDelete();
    }

    public function bulkUpdate(string $risId, array $rowsById): int
    {
        $count = 0;

        $items = RisItem::query()
            ->where('ris_id', $risId)
            ->whereIn('id', array_keys($rowsById))
            ->get();

        foreach ($items as $it) {
            $row = $rowsById[(string) $it->id] ?? null;
            if (!$row) continue;

            $it->qty_requested = (int) ($row['qty_requested'] ?? $it->qty_requested);
            $it->remarks = array_key_exists('remarks', $row) ? ($row['remarks'] ?? null) : $it->remarks;

            if ($it->isDirty()) {
                $it->save();
                $count++;
            }
        }

        return $count;
    }

        public function createMany(string $risId, array $itemsPayload): void
    {
        $now = now();

        $rows = array_map(function ($r) use ($risId, $now) {
            return array_merge([
                'id' => (string) Str::uuid(),
                'ris_id' => $risId,
                'created_at' => $now,
                'updated_at' => $now,
            ], $r);
        }, $itemsPayload);

        if (!empty($rows)) {
            DB::table('ris_items')->insert($rows);
        }
    }

    

}