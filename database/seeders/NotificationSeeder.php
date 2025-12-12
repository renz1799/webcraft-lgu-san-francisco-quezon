<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $actorUserId = '581161f6-2e30-4c37-888f-f4aabe2bfa90';
        $notifiableUserId = '44018b89-a287-4b86-a095-e8cc10a5e803';

        // Unread notification
        Notification::factory()->create([
            'id' => (string) Str::uuid(),
            'actor_user_id' => $actorUserId,
            'notifiable_user_id' => $notifiableUserId,

            'type' => 'task_assigned',
            'title' => 'Task Assigned',
            'message' => 'A task was assigned to you by another user.',

            'entity_type' => 'tasks',
            'entity_id' => (string) Str::uuid(),

            'data' => [
                'task_title' => 'Prepare Enrollment Report',
                'url' => '/tasks/sample-task-id',
            ],
        ]);

        // Read notification
        Notification::factory()
            ->read()
            ->create([
                'id' => (string) Str::uuid(),
                'actor_user_id' => $actorUserId,
                'notifiable_user_id' => $notifiableUserId,

                'type' => 'task_assigned',
                'title' => 'Another Task Assigned',
                'message' => 'Another task has been assigned to you.',

                'entity_type' => 'tasks',
                'entity_id' => (string) Str::uuid(),

                'data' => [
                    'task_title' => 'Verify Student Records',
                    'url' => '/tasks/another-task-id',
                ],
            ]);
    }
}

//php artisan db:seed --class=NotificationSeeder
