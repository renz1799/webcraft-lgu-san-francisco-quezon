<?php

namespace Database\Seeders\Modules\GSO;

use Illuminate\Database\Seeder;
use App\Modules\GSO\Models\AssetCategory;
use App\Modules\GSO\Models\AssetType;
use Illuminate\Support\Str;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $ppe = AssetType::where('type_code','PPE')->first();
        $ics = AssetType::where('type_code','ICS')->first();

        $categories = [

            [
                'type'=>$ppe,
                'code'=>'PPE-01',
                'name'=>'Office Equipment',
                'account'=>'10605010'
            ],

            [
                'type'=>$ppe,
                'code'=>'PPE-02',
                'name'=>'ICT Equipment',
                'account'=>'10605030'
            ],

            [
                'type'=>$ics,
                'code'=>'ICS-01',
                'name'=>'Office Supplies',
                'account'=>null
            ],

        ];

        foreach ($categories as $category)
        {
            AssetCategory::updateOrCreate(

                ['asset_code'=>$category['code']],

                [
                    'id'=>Str::uuid(),
                    'asset_type_id'=>$category['type']->id,
                    'asset_name'=>$category['name'],
                    'account_group'=>$category['account'],
                ]
            );
        }
    }
}