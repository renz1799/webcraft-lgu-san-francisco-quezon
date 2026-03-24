<?php

namespace Database\Seeders\Modules\GSO;

use Illuminate\Database\Seeder;
use App\Modules\GSO\Models\FundCluster;
use Illuminate\Support\Str;

class FundClusterSeeder extends Seeder
{
    public function run(): void
    {
        $clusters = [

            ['code'=>'101','name'=>'General Fund'],
            ['code'=>'102','name'=>'Trust Fund']

        ];

        foreach($clusters as $cluster)
        {
            FundCluster::updateOrCreate(

                ['code'=>$cluster['code']],

                [
                    'id'=>Str::uuid(),
                    'name'=>$cluster['name'],
                    'is_active'=>true
                ]

            );
        }
    }
}