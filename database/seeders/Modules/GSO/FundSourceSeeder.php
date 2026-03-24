<?php

namespace Database\Seeders\Modules\GSO;

use Illuminate\Database\Seeder;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\FundCluster;
use Illuminate\Support\Str;

class FundSourceSeeder extends Seeder
{
    public function run(): void
    {
        $general = FundCluster::where('code','101')->first();

        $sources = [

            [
                'code'=>'GF-001',
                'name'=>'General Appropriation',
                'cluster'=>$general
            ],

        ];

        foreach($sources as $source)
        {
            FundSource::updateOrCreate(

                ['code'=>$source['code']],

                [
                    'id'=>Str::uuid(),
                    'name'=>$source['name'],
                    'fund_cluster_id'=>$source['cluster']->id,
                    'is_active'=>true
                ]

            );
        }
    }
}