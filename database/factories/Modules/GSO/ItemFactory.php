<?php

namespace Database\Factories\Modules\GSO;

use App\Modules\GSO\Models\AssetCategory;
use App\Modules\GSO\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        $trackingType = $this->faker->randomElement(['consumable', 'property']);
        $requiresSerial = $trackingType === 'property'
            ? $this->faker->boolean(70)
            : false;

        return [
            'asset_id' => AssetCategory::factory(),
            'item_name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->sentence(12),
            'base_unit' => $this->faker->randomElement(['piece', 'unit', 'ream', 'box', 'roll', 'bottle']),
            'item_identification' => strtoupper($this->faker->bothify('ITM-###')),
            'major_sub_account_group' => $this->faker->randomElement([
                'Office Supplies',
                'Small Tools and Equipment',
                'ICT Equipment',
                'Office Equipment',
            ]),
            'tracking_type' => $trackingType,
            'requires_serial' => $requiresSerial,
            'is_semi_expendable' => $trackingType === 'property'
                ? $this->faker->boolean(35)
                : false,
            'is_selected' => false,
        ];
    }
}