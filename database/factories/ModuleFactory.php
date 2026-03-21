<?php

namespace Database\Factories;

use App\Core\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    protected $model = Module::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('MOD???')),
            'name' => fake()->company() . ' Module',
            'description' => fake()->sentence(),
            'url' => fake()->url(),
            'default_department_id' => null,
            'is_active' => true,
        ];
    }
}
