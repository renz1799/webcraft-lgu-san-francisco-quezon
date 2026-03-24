<?php

namespace Database\Factories\Modules\GSO;

use App\Modules\GSO\Models\AssetCategory;
use App\Modules\GSO\Models\AssetType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetCategoryFactory extends Factory
{
    protected $model = AssetCategory::class;

    public function definition(): array
    {
        $typeCode = $this->faker->randomElement(['PPE', 'ICS', 'CONS', 'INTA', 'LAND', 'INFRA']);

        return [
            'asset_type_id' => AssetType::factory()->state([
                'type_code' => $typeCode,
                'type_name' => match ($typeCode) {
                    'PPE' => 'Property, Plant and Equipment',
                    'ICS' => 'Inventory Custodian Slip',
                    'CONS' => 'Consumables',
                    'INTA' => 'Intangible Assets',
                    'LAND' => 'Land Property',
                    'INFRA' => 'Infrastructure Assets',
                },
            ]),
            'asset_code' => $typeCode . '-' . $this->faker->numerify('##'),
            'asset_name' => $this->faker->words(2, true),
            'account_group' => $this->faker->randomElement(['Non-current', 'Current']),
            'is_selected' => false,
        ];
    }
}