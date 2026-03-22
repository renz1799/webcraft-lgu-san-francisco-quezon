<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\ItemUnitConversion;

interface ItemUnitConversionRepositoryInterface
{
    /**
     * @return array<int, ItemUnitConversion>
     */
    public function getByItemId(string $itemId): array;

    public function softDeleteMissing(string $itemId, array $keepFromUnits): int;

    public function upsertOne(string $itemId, string $fromUnit, int $multiplier): ItemUnitConversion;
}
