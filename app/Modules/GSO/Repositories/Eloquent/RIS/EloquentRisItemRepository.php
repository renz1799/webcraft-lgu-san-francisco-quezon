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

        foreach ($items as $item) {
            $row = $rowsById[(string) $item->id] ?? null;

            if (!is_array($row)) {
                continue;
            }

            $this->applyBulkUpdatePayload($item, $row);

            if ($item->isDirty()) {
                $item->save();
                $count++;
            }
        }

        return $count;
    }

    public function createMany(string $risId, array $itemsPayload): void
    {
        $rows = $this->buildCreateManyRows($risId, $itemsPayload);

        if ($rows === []) {
            return;
        }

        DB::table('ris_items')->insert($rows);
    }

    public function existsActiveByRisIdAndItemId(string $risId, string $itemId): bool
    {
        return RisItem::query()
            ->where('ris_id', $risId)
            ->where('item_id', $itemId)
            ->whereNull('deleted_at')
            ->exists();
    }

    public function nextLineNumber(string $risId): int
    {
        return ((int) (
            RisItem::query()
                ->where('ris_id', $risId)
                ->whereNull('deleted_at')
                ->max('line_no') ?? 0
        )) + 1;
    }

    public function listActiveItemIdsByRisId(string $risId): array
    {
        return RisItem::query()
            ->where('ris_id', $risId)
            ->whereNull('deleted_at')
            ->pluck('item_id')
            ->map(fn ($value) => (string) $value)
            ->all();
    }

    private function applyBulkUpdatePayload(RisItem $item, array $row): void
    {
        $item->qty_requested = (int) ($row['qty_requested'] ?? $item->qty_requested);

        if (array_key_exists('remarks', $row)) {
            $item->remarks = $row['remarks'] ?? null;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildCreateManyRows(string $risId, array $itemsPayload): array
    {
        $now = now();

        return array_map(function (array $row) use ($risId, $now): array {
            return array_merge([
                'id' => (string) Str::uuid(),
                'ris_id' => $risId,
                'created_at' => $now,
                'updated_at' => $now,
            ], $row);
        }, $itemsPayload);
    }
}