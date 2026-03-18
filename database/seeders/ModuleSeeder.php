<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        Module::updateOrCreate(
            ['id' => config('module.id')],
            [
                'code' => config('module.code'),
                'name' => config('module.name'),
                'description' => 'System module seeded from application configuration.',
                'url' => config('app.url'),
                'is_active' => true,
            ]
        );
    }
}