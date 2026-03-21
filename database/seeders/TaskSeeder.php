<?php

namespace Database\Seeders;

use App\Core\Models\Notification;
use App\Core\Models\User;
use App\Core\Support\CurrentContext;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $context = app(CurrentContext::class);

        $moduleId = $context->moduleId();
        $departmentId = $context->defaultDepartmentId();

        if (! $moduleId) {
            throw new \RuntimeException('TaskSeeder: current module not found. Run ModuleSeeder first.');
        }

        if (! $departmentId) {
            throw new \RuntimeException('TaskSeeder: default department not found. Run DepartmentSeeder first.');
        }

        $admin = User::query()
            ->where('email', 'admin@webcraft.ph')
            ->first();

        if (! $admin) {
            throw new \RuntimeException('TaskSeeder: admin user not found. Run UserSeeder first.');
        }

        $sampleStaff = User::query()
            ->where('email', '!=', 'admin@webcraft.ph')
            ->orderBy('created_at')
            ->first();

        if (! $sampleStaff) {
            throw new \RuntimeException('TaskSeeder: no staff user found. Seed staff users first.');
        }

        DB::transaction(function () use ($moduleId, $departmentId, $admin, $sampleStaff) {
            TaskEvent::query()->forceDelete();
            Task::query()->forceDelete();
            Notification::query()->forceDelete();

            $t1 = Task::create([
                'module_id' => $moduleId,
                'department_id' => $departmentId,
                'title' => 'Release Sticker (Inventory Item)',
                'description' => 'Generate and release property sticker for newly received item.',
                'type' => 'release_sticker',
                'status' => Task::STATUS_PENDING,
                'priority' => 1,
                'created_by_user_id' => $admin->id,
                'assigned_to_user_id' => $admin->id,
                'subject_type' => 'inventory_items',
                'subject_id' => null,
                'data' => [
                    'subject_url' => '/inventory/items/demo-item-uuid',
                ],
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]);

            $this->seedEventsForTask($t1, $admin->id, [
                ['event_type' => 'created', 'note' => 'Task created.', 'at' => now()->subDays(2)],
                ['event_type' => 'assigned', 'note' => 'Assigned to admin.', 'meta' => ['to_user_id' => $admin->id], 'at' => now()->subDays(2)],
            ]);

            $this->seedNotificationForTask($t1, $moduleId, $departmentId, $admin->id, $admin->id, 'assigned');

            $t2 = Task::create([
                'module_id' => $moduleId,
                'department_id' => $departmentId,
                'title' => 'Print WeBill',
                'description' => 'Print WeBill for customer pickup.',
                'type' => 'print_we_bill',
                'status' => Task::STATUS_IN_PROGRESS,
                'priority' => 0,
                'created_by_user_id' => $admin->id,
                'assigned_to_user_id' => $admin->id,
                'started_at' => now()->subDay(),
                'data' => [
                    'subject_url' => '/billing/webills/demo-webill',
                ],
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDay(),
            ]);

            $this->seedEventsForTask($t2, $admin->id, [
                ['event_type' => 'created', 'note' => 'Task created.', 'at' => now()->subDays(3)],
                ['event_type' => 'assigned', 'note' => 'Assigned to admin.', 'meta' => ['to_user_id' => $admin->id], 'at' => now()->subDays(3)],
                ['event_type' => 'status_changed', 'from' => 'pending', 'to' => 'in_progress', 'note' => 'Started printing.', 'at' => now()->subDay()],
                ['event_type' => 'comment', 'note' => 'Need signature from officer-in-charge.', 'at' => now()->subHours(18)],
            ]);

            $this->seedNotificationForTask($t2, $moduleId, $departmentId, $admin->id, $admin->id, 'assigned');

            $t3 = Task::create([
                'module_id' => $moduleId,
                'department_id' => $departmentId,
                'title' => 'Release Documents (DV/ORS)',
                'description' => 'Prepare and release required documents.',
                'type' => 'release_document',
                'status' => Task::STATUS_DONE,
                'priority' => 0,
                'created_by_user_id' => $admin->id,
                'assigned_to_user_id' => $admin->id,
                'started_at' => now()->subDays(5),
                'completed_at' => now()->subDays(4),
                'data' => [
                    'subject_url' => '/documents/demo',
                ],
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(4),
            ]);

            $this->seedEventsForTask($t3, $admin->id, [
                ['event_type' => 'created', 'note' => 'Task created.', 'at' => now()->subDays(6)],
                ['event_type' => 'assigned', 'note' => 'Assigned to admin.', 'meta' => ['to_user_id' => $admin->id], 'at' => now()->subDays(6)],
                ['event_type' => 'status_changed', 'from' => 'pending', 'to' => 'in_progress', 'note' => 'Started processing documents.', 'at' => now()->subDays(5)],
                ['event_type' => 'status_changed', 'from' => 'in_progress', 'to' => 'done', 'note' => 'Ready to pick up.', 'at' => now()->subDays(4)],
            ]);

            $this->seedNotificationForTask($t3, $moduleId, $departmentId, $admin->id, $admin->id, 'assigned');

            $p1 = Task::create([
                'module_id' => $moduleId,
                'department_id' => $departmentId,
                'title' => 'Pooled: Release Sticker (Any Admin can claim)',
                'description' => 'A pooled task visible to admin role; anyone can claim.',
                'type' => 'release_sticker',
                'status' => Task::STATUS_PENDING,
                'priority' => 0,
                'created_by_user_id' => $admin->id,
                'assigned_to_user_id' => null,
                'data' => [
                    'eligible_roles' => ['Administrator'],
                    'subject_url' => '/inventory/items/demo-item-uuid-2',
                ],
                'created_at' => now()->subHours(10),
                'updated_at' => now()->subHours(10),
            ]);

            $this->seedEventsForTask($p1, $admin->id, [
                ['event_type' => 'created', 'note' => 'Pooled task created.', 'at' => now()->subHours(10)],
            ]);

            $this->seedNotificationForTask($p1, $moduleId, $departmentId, $admin->id, $admin->id, 'pooled');

            $p2 = Task::create([
                'module_id' => $moduleId,
                'department_id' => $departmentId,
                'title' => 'Pooled: Print Gate Pass (Admin/Staff)',
                'description' => 'Visible to admin and staff roles.',
                'type' => 'print_gate_pass',
                'status' => Task::STATUS_PENDING,
                'priority' => 1,
                'created_by_user_id' => $admin->id,
                'assigned_to_user_id' => null,
                'due_at' => now()->addDay(),
                'data' => [
                    'eligible_roles' => ['Administrator', 'Staff'],
                    'subject_url' => '/gatepass/demo',
                ],
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ]);

            $this->seedEventsForTask($p2, $admin->id, [
                ['event_type' => 'created', 'note' => 'Pooled task created.', 'at' => now()->subHours(3)],
            ]);

            $this->seedEventsForTask($p2, $sampleStaff->id, [
                ['event_type' => 'comment', 'note' => 'Staff noted: Waiting for supporting document.', 'at' => now()->subHours(2)],
            ]);

            $this->seedNotificationForTask($p2, $moduleId, $departmentId, $admin->id, $admin->id, 'pooled');

            $p3 = Task::create([
                'module_id' => $moduleId,
                'department_id' => $departmentId,
                'title' => 'Pooled: Staff-only Task (should not show for admin if strict)',
                'description' => 'Visible only to staff role.',
                'type' => 'staff_only_demo',
                'status' => Task::STATUS_PENDING,
                'priority' => 0,
                'created_by_user_id' => $admin->id,
                'assigned_to_user_id' => null,
                'data' => [
                    'eligible_roles' => ['Staff'],
                ],
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ]);

            $this->seedEventsForTask($p3, $sampleStaff->id, [
                ['event_type' => 'created', 'note' => 'Staff-only pooled task created.', 'at' => now()->subHours(1)],
            ]);
        });
    }

    private function seedEventsForTask(Task $task, string $actorUserId, array $events): void
    {
        $actor = User::with('profile')->find($actorUserId);

        $actorName = $actor?->profile?->full_name
            ?: ($actor?->username ?: 'Unknown User');

        $actorUsername = $actor?->username;

        foreach ($events as $event) {
            TaskEvent::create([
                'task_id' => $task->id,
                'actor_user_id' => $actorUserId,
                'actor_name_snapshot' => $actorName,
                'actor_username_snapshot' => $actorUsername,
                'event_type' => $event['event_type'],
                'from_status' => $event['from'] ?? null,
                'to_status' => $event['to'] ?? null,
                'note' => $event['note'] ?? null,
                'meta' => $event['meta'] ?? null,
                'created_at' => $event['at'] ?? now(),
                'updated_at' => $event['at'] ?? now(),
            ]);
        }
    }

    private function seedNotificationForTask(
        Task $task,
        string $moduleId,
        string $departmentId,
        string $recipientUserId,
        string $actorUserId,
        string $kind = 'assigned'
    ): void {
        $type = match ($kind) {
            'assigned' => 'task.assigned',
            'pooled' => 'task.available',
            default => 'task.updated',
        };

        Notification::create([
            'module_id' => $moduleId,
            'department_id' => $departmentId,
            'notifiable_user_id' => $recipientUserId,
            'actor_user_id' => $actorUserId,
            'type' => $type,
            'title' => $kind === 'pooled' ? 'New Task Available' : 'New Task Assigned',
            'message' => $task->title,
            'entity_type' => 'task',
            'entity_id' => $task->id,
            'data' => [
                'url' => route('tasks.show', $task->id),
                'task_id' => $task->id,
                'status' => $task->status,
            ],
            'read_at' => null,
        ]);
    }
}
