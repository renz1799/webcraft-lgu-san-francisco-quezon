<?php

namespace Database\Seeders;

use App\Core\Models\Notification;
use App\Core\Models\User;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $context = app(CurrentContext::class);

        $moduleId = $context->moduleId();
        $departmentId = $context->defaultDepartmentId();

        if (! $moduleId) {
            throw new \RuntimeException('NotificationSeeder: current module not found. Run ModuleSeeder first.');
        }

        if (! $departmentId) {
            throw new \RuntimeException('NotificationSeeder: default department not found. Run DepartmentSeeder first.');
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
            'module_id' => $moduleId,
            'department_id' => $departmentId,
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
                'module_id' => $moduleId,
                'department_id' => $departmentId,
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
