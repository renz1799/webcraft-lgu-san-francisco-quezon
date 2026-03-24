<?php

namespace Database\Seeders\Modules\GSO;

use Illuminate\Database\Seeder;
use App\Modules\GSO\Models\AssetType;
use Illuminate\Support\Str;

class AssetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'PPE', 'name' => 'Property, Plant and Equipment'],
            ['code' => 'ICS', 'name' => 'Inventory Custodian Slip'],
        ];

        foreach ($types as $type) {
            AssetType::updateOrCreate(
                ['type_code' => $type['code']],
                [
                    'id' => Str::uuid(),
                    'type_name' => $type['name'],
                ]
            );
        }
    }
}