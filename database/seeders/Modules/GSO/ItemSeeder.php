<?php

namespace Database\Seeders\Modules\GSO;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Models\AssetCategory;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $officeSupplies = AssetCategory::where('asset_code', 'ICS-01')->firstOrFail();
        $ictEquipment = AssetCategory::where('asset_code', 'PPE-02')->firstOrFail();

        $items = [
            [
                'asset_id' => $officeSupplies->id,
                'item_name' => 'Bond Paper A4',
                'description' => '80gsm copy paper',
                'base_unit' => 'ream',
                'item_identification' => 'ICS-BOND-A4',
                'major_sub_account_group' => 'Office Supplies',
                'tracking_type' => 'consumable',
                'requires_serial' => false,
                'is_semi_expendable' => false,
            ],
            [
                'asset_id' => $officeSupplies->id,
                'item_name' => 'Ballpen Blue',
                'description' => 'Standard office pen',
                'base_unit' => 'piece',
                'item_identification' => 'ICS-BP-BLUE',
                'major_sub_account_group' => 'Office Supplies',
                'tracking_type' => 'consumable',
                'requires_serial' => false,
                'is_semi_expendable' => false,
            ],
            [
                'asset_id' => $officeSupplies->id,
                'item_name' => 'Printer Ink',
                'description' => 'Black cartridge',
                'base_unit' => 'piece',
                'item_identification' => 'ICS-INK-BLK',
                'major_sub_account_group' => 'Office Supplies',
                'tracking_type' => 'consumable',
                'requires_serial' => false,
                'is_semi_expendable' => false,
            ],
            [
                'asset_id' => $ictEquipment->id,
                'item_name' => 'Laptop',
                'description' => 'Portable computer',
                'base_unit' => 'unit',
                'item_identification' => 'PPE-LAPTOP',
                'major_sub_account_group' => 'ICT Equipment',
                'tracking_type' => 'property',
                'requires_serial' => true,
                'is_semi_expendable' => false,
            ],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(
                ['item_name' => $item['item_name']],
                [
                    'id' => (string) Str::uuid(),
                    'asset_id' => $item['asset_id'],
                    'description' => $item['description'],
                    'base_unit' => $item['base_unit'],
                    'item_identification' => $item['item_identification'],
                    'major_sub_account_group' => $item['major_sub_account_group'],
                    'tracking_type' => $item['tracking_type'],
                    'requires_serial' => $item['requires_serial'],
                    'is_semi_expendable' => $item['is_semi_expendable'],
                    'is_selected' => false,
                ]
            );
        }
    }
}