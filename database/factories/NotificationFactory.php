<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Module;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),

            'module_id' => Module::factory(),
            'department_id' => Department::factory(),

            'notifiable_user_id' => User::factory(),
            'actor_user_id' => User::factory(),

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

    public function read(): static
    {
        return $this->state(fn () => [
            'read_at' => now(),
        ]);
    }

    public function unread(): static
    {
        return $this->state(fn () => [
            'read_at' => null,
        ]);
    }

    public function forModule(string $moduleId): static
    {
        return $this->state(fn () => [
            'module_id' => $moduleId,
        ]);
    }

    public function forDepartment(string $departmentId): static
    {
        return $this->state(fn () => [
            'department_id' => $departmentId,
        ]);
    }

    public function forRecipient(string $userId): static
    {
        return $this->state(fn () => [
            'notifiable_user_id' => $userId,
        ]);
    }

    public function fromActor(?string $userId): static
    {
        return $this->state(fn () => [
            'actor_user_id' => $userId,
        ]);
    }
}