<?php

namespace App\Modules\GSO\Services\Inventory;

use Illuminate\Support\Facades\DB;

class ItemUnitConversionService
{
    public function toBaseQty(string $itemId, int $qty, ?string $fromUnit): array
    {
        $qty = max(0, (int) $qty);
        $fromUnit = trim((string) ($fromUnit ?? ''));

        $baseUnit = DB::table('items')
            ->where('id', $itemId)
            ->whereNull('deleted_at')
            ->value('base_unit');

        $baseUnit = $baseUnit !== null ? trim((string) $baseUnit) : null;

        if ($qty === 0) {
            return ['baseQty' => 0, 'multiplier' => 1, 'baseUnit' => $baseUnit];
        }

        if ($fromUnit === '' || ($baseUnit && strcasecmp($fromUnit, $baseUnit) === 0)) {
            return ['baseQty' => $qty, 'multiplier' => 1, 'baseUnit' => $baseUnit];
        }

        $multiplier = (int) (DB::table('item_unit_conversions')
            ->where('item_id', $itemId)
            ->where('from_unit', $fromUnit)
            ->whereNull('deleted_at')
            ->value('multiplier') ?? 1);

        if ($multiplier <= 0) {
            $multiplier = 1;
        }

        return [
            'baseQty' => $qty * $multiplier,
            'multiplier' => $multiplier,
            'baseUnit' => $baseUnit,
        ];
    }
}
