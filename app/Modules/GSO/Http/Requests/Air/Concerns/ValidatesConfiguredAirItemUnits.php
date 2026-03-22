<?php

namespace App\Modules\GSO\Http\Requests\Air\Concerns;

use App\Modules\GSO\Models\AirItem;
use App\Modules\GSO\Models\Item;
use Illuminate\Validation\Validator;

trait ValidatesConfiguredAirItemUnits
{
    protected function findItemForUnitValidation(?string $itemId): ?Item
    {
        $itemId = trim((string) ($itemId ?? ''));

        if ($itemId === '') {
            return null;
        }

        return Item::query()
            ->with([
                'unitConversions' => fn ($query) => $query
                    ->select(['id', 'item_id', 'from_unit', 'multiplier']),
            ])
            ->find($itemId);
    }

    protected function findAirItemForUnitValidation(?string $airItemId, ?string $airId = null): ?AirItem
    {
        $airItemId = trim((string) ($airItemId ?? ''));

        if ($airItemId === '') {
            return null;
        }

        $query = AirItem::query()
            ->with([
                'item' => fn ($builder) => $builder
                    ->withTrashed()
                    ->select(['id', 'base_unit']),
                'item.unitConversions' => fn ($builder) => $builder
                    ->select(['id', 'item_id', 'from_unit', 'multiplier']),
            ]);

        $airId = trim((string) ($airId ?? ''));
        if ($airId !== '') {
            $query->where('air_id', $airId);
        }

        return $query->find($airItemId);
    }

    protected function validateConfiguredUnitSelection(Validator $validator, string $field, ?Item $item, ?string $selectedUnit): void
    {
        if (! $item) {
            return;
        }

        $options = $item->getAvailableUnitOptions();

        if ($options === []) {
            $validator->errors()->add($field, 'This item has no configured units. Update the item setup first.');
            return;
        }

        $selectedUnit = trim((string) ($selectedUnit ?? ''));

        if ($selectedUnit === '') {
            $validator->errors()->add($field, 'Unit is required.');
            return;
        }

        if ($item->canonicalUnitValue($selectedUnit) !== null) {
            return;
        }

        $allowed = implode(', ', array_map(
            static fn (array $option): string => (string) ($option['value'] ?? ''),
            $options
        ));

        $validator->errors()->add(
            $field,
            "Unit \"{$selectedUnit}\" is not configured for this item. Choose one of: {$allowed}."
        );
    }
}
