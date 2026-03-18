<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Module;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $module = Module::query()->find(config('module.id'));

        if (! $module) {
            throw new \RuntimeException('NotificationSeeder: current module not found. Run ModuleSeeder first.');
        }

        $department = Department::query()->orderBy('created_at')->first();

        if (! $department) {
            throw new \RuntimeException('NotificationSeeder: no department found. Seed departments first.');
        }

        $actor = User::query()
            ->where('email', 'admin@webcraft.ph')
            ->first();

        $recipient = User::query()
            ->where('email', '!=', 'admin@webcraft.ph')
            ->orderBy('created_at')
            ->first();

        if (! $actor || ! $recipient) {
            throw new \RuntimeException('NotificationSeeder: required users not found. Run UserSeeder first.');
        }

        Notification::factory()->create([
            'id' => (string) Str::uuid(),
            'module_id' => $module->id,
            'department_id' => $department->id,
            'actor_user_id' => $actor->id,
            'notifiable_user_id' => $recipient->id,
            'type' => 'task.assigned',
            'title' => 'Task Assigned',
            'message' => 'A task was assigned to you by another user.',
            'entity_type' => 'task',
            'entity_id' => (string) Str::uuid(),
            'data' => [
                'task_title' => 'Prepare Enrollment Report',
                'url' => '/tasks/sample-task-id',
            ],
        ]);

        Notification::factory()
            ->read()
            ->create([
                'id' => (string) Str::uuid(),
                'module_id' => $module->id,
                'department_id' => $department->id,
                'actor_user_id' => $actor->id,
                'notifiable_user_id' => $recipient->id,
                'type' => 'task.assigned',
                'title' => 'Another Task Assigned',
                'message' => 'Another task has been assigned to you.',
                'entity_type' => 'task',
                'entity_id' => (string) Str::uuid(),
                'data' => [
                    'task_title' => 'Verify Student Records',
                    'url' => '/tasks/another-task-id',
                ],
            ]);
    }
}