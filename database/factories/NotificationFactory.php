<?php

namespace Database\Factories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),

            // default values (can be overridden in seeder)
            'notifiable_user_id' => (string) Str::uuid(),
            'actor_user_id' => (string) Str::uuid(),

            'type' => 'task_assigned',
            'title' => 'New Task Assigned',
            'message' => 'You have been assigned a new task.',

            'entity_type' => 'tasks',
            'entity_id' => (string) Str::uuid(),

            'data' => [
                'task_title' => $this->faker->sentence(3),
                'url' => '/tasks/' . Str::uuid(),
            ],

            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Mark notification as read
     */
    public function read(): static
    {
        return $this->state(fn () => [
            'read_at' => now(),
        ]);
    }
}
