<?php

namespace Database\Factories;

use App\Core\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('DEPT???')),
            'name' => fake()->company() . ' Office',
            'short_name' => fake()->optional()->word(),
            'type' => fake()->randomElement(['office', 'division', 'section', 'unit']),
            'parent_department_id' => null,
            'head_user_id' => null,
            'is_active' => true,
        ];
    }
}