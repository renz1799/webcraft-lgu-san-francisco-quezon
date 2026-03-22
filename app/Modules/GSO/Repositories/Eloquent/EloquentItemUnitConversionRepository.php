<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\ItemUnitConversion;
use App\Modules\GSO\Repositories\Contracts\ItemUnitConversionRepositoryInterface;

class EloquentItemUnitConversionRepository implements ItemUnitConversionRepositoryInterface
{
    public function getByItemId(string $itemId): array
    {
        return ItemUnitConversion::query()
            ->where('item_id', $itemId)
            ->orderBy('from_unit')
            ->get()
            ->all();
    }

    public function softDeleteMissing(string $itemId, array $keepFromUnits): int
    {
        $keepFromUnits = array_values(array_filter(array_map(
            fn (mixed $unit): string => strtolower(trim((string) $unit)),
            $keepFromUnits,
        )));

        $query = ItemUnitConversion::query()
            ->where('item_id', $itemId);

        if ($keepFromUnits !== []) {
            $query->whereNotIn('from_unit', $keepFromUnits);
        }

        return $query->delete();
    }

    public function upsertOne(string $itemId, string $fromUnit, int $multiplier): ItemUnitConversion
    {
        $fromUnit = strtolower(trim($fromUnit));

        $conversion = ItemUnitConversion::query()
            ->withTrashed()
            ->where('item_id', $itemId)
            ->where('from_unit', $fromUnit)
            ->first();

        if (! $conversion) {
            return ItemUnitConversion::query()->create([
                'item_id' => $itemId,
                'from_unit' => $fromUnit,
                'multiplier' => $multiplier,
            ]);
        }

        if ($conversion->trashed()) {
            $conversion->restore();
        }

        $conversion->multiplier = $multiplier;
        $conversion->save();

        return $conversion->refresh();
    }
}
