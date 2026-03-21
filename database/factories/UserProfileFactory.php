<?php

namespace Database\Factories;

use App\Core\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional()->firstName(),
            'last_name' => fake()->lastName(),
            'name_extension' => fake()->optional()->randomElement(['Jr.', 'Sr.', 'III', 'IV']),
            'address' => fake()->address(),
            'contact_details' => fake()->phoneNumber(),
            'profile_photo_path' => null,
        ];
    }
}