<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'user_type' => 'Viewer',
            'is_active' => true,
            'must_change_password' => false,
            'primary_department_id' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function mustChangePassword(): static
    {
        return $this->state(fn () => [
            'must_change_password' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    public function forDepartment(string $departmentId): static
    {
        return $this->state(fn () => [
            'primary_department_id' => $departmentId,
        ]);
    }

    public function withPrimaryDepartment(): static
    {
        return $this->state(fn () => [
            'primary_department_id' => Department::factory(),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'username' => 'admin',
            'email' => 'admin@webcraft.ph',
            'password' => Hash::make('password'),
            'user_type' => 'Admin',
            'is_active' => true,
            'must_change_password' => false,
        ]);
    }
}