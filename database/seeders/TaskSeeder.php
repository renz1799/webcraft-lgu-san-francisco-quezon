<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\TaskEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $adminUserId = '44018b89-a287-4b86-a095-e8cc10a5e803';

        DB::transaction(function () use ($adminUserId) {

            // Optional: clear old data (ONLY for local dev)
            TaskEvent::query()->delete();
            Task::query()->delete();

            // ----------------------------
            // 1) Assigned tasks (My Tasks)
            // ----------------------------

            $t1 = Task::create([
                'title' => 'Release Sticker (Inventory Item)',
                'description' => 'Generate and release property sticker for newly received item.',
                'type' => 'release_sticker',
                'status' => Task::STATUS_PENDING,
                'priority' => 1,
                'created_by_user_id' => $adminUserId,
                'assigned_to_user_id' => $adminUserId,
                'subject_type' => 'inventory_items',
                'subject_id' => null, // you can put real UUID later
                'data' => [
                    'subject_url' => '/inventory/items/demo-item-uuid',
                ],
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]);

            $this->seedEventsForTask($t1, $adminUserId, [
                ['event_type' => 'created', 'note' => 'Task created.', 'at' => now()->subDays(2)],
                ['event_type' => 'assigned', 'note' => 'Assigned to admin.', 'meta' => ['to_user_id' => $adminUserId], 'at' => now()->subDays(2)],
            ]);

            $t2 = Task::create([
                'title' => 'Print WeBill',
                'description' => 'Print WeBill for customer pickup.',
                'type' => 'print_we_bill',
                'status' => Task::STATUS_IN_PROGRESS,
                'priority' => 0,
                'created_by_user_id' => $adminUserId,
                'assigned_to_user_id' => $adminUserId,
                'started_at' => now()->subDay(),
                'data' => [
                    'subject_url' => '/billing/webills/demo-webill',
                ],
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDay(),
            ]);

            $this->seedEventsForTask($t2, $adminUserId, [
                ['event_type' => 'created', 'note' => 'Task created.', 'at' => now()->subDays(3)],
                ['event_type' => 'assigned', 'note' => 'Assigned to admin.', 'meta' => ['to_user_id' => $adminUserId], 'at' => now()->subDays(3)],
                ['event_type' => 'status_changed', 'from' => 'pending', 'to' => 'in_progress', 'note' => 'Started printing.', 'at' => now()->subDay()],
                ['event_type' => 'comment', 'note' => 'Need signature from officer-in-charge.', 'at' => now()->subHours(18)],
            ]);

            $t3 = Task::create([
                'title' => 'Release Documents (DV/ORS)',
                'description' => 'Prepare and release required documents.',
                'type' => 'release_document',
                'status' => Task::STATUS_DONE,
                'priority' => 0,
                'created_by_user_id' => $adminUserId,
                'assigned_to_user_id' => $adminUserId,
                'started_at' => now()->subDays(5),
                'completed_at' => now()->subDays(4),
                'data' => [
                    'subject_url' => '/documents/demo',
                ],
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(4),
            ]);

            $this->seedEventsForTask($t3, $adminUserId, [
                ['event_type' => 'created', 'note' => 'Task created.', 'at' => now()->subDays(6)],
                ['event_type' => 'assigned', 'note' => 'Assigned to admin.', 'meta' => ['to_user_id' => $adminUserId], 'at' => now()->subDays(6)],
                ['event_type' => 'status_changed', 'from' => 'pending', 'to' => 'in_progress', 'note' => 'Started processing documents.', 'at' => now()->subDays(5)],
                ['event_type' => 'status_changed', 'from' => 'in_progress', 'to' => 'done', 'note' => 'Ready to pick up.', 'at' => now()->subDays(4)],
            ]);

            // -----------------------------------------
            // 2) Available tasks (Role-based / pooled)
            // -----------------------------------------

            $p1 = Task::create([
                'title' => 'Pooled: Release Sticker (Any Admin can claim)',
                'description' => 'A pooled task visible to admin role; anyone can claim.',
                'type' => 'release_sticker',
                'status' => Task::STATUS_PENDING,
                'priority' => 0,
                'created_by_user_id' => $adminUserId,
                'assigned_to_user_id' => null,
                'data' => [
                    'eligible_roles' => ['admin'],
                    'subject_url' => '/inventory/items/demo-item-uuid-2',
                ],
                'created_at' => now()->subHours(10),
                'updated_at' => now()->subHours(10),
            ]);

            $this->seedEventsForTask($p1, $adminUserId, [
                ['event_type' => 'created', 'note' => 'Pooled task created.', 'at' => now()->subHours(10)],
            ]);

            $p2 = Task::create([
                'title' => 'Pooled: Print Gate Pass (Admin/Staff)',
                'description' => 'Visible to admin and staff roles.',
                'type' => 'print_gate_pass',
                'status' => Task::STATUS_PENDING,
                'priority' => 1,
                'created_by_user_id' => $adminUserId,
                'assigned_to_user_id' => null,
                'due_at' => now()->addDay(),
                'data' => [
                    'eligible_roles' => ['admin', 'staff'],
                    'subject_url' => '/gatepass/demo',
                ],
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ]);

            $this->seedEventsForTask($p2, $adminUserId, [
                ['event_type' => 'created', 'note' => 'Pooled task created.', 'at' => now()->subHours(3)],
                ['event_type' => 'comment', 'note' => 'Waiting for supporting document.', 'at' => now()->subHours(2)],
            ]);

            // This one will NOT show for admin if your available query checks eligible_roles strictly
            $p3 = Task::create([
                'title' => 'Pooled: Staff-only Task (should not show for admin if strict)',
                'description' => 'Visible only to staff role.',
                'type' => 'staff_only_demo',
                'status' => Task::STATUS_PENDING,
                'priority' => 0,
                'created_by_user_id' => $adminUserId,
                'assigned_to_user_id' => null,
                'data' => [
                    'eligible_roles' => ['staff'],
                ],
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ]);

            $this->seedEventsForTask($p3, $adminUserId, [
                ['event_type' => 'created', 'note' => 'Staff-only pooled task created.', 'at' => now()->subHours(1)],
            ]);
        });
    }

    private function seedEventsForTask(Task $task, string $actorUserId, array $events): void
    {
        foreach ($events as $e) {
            TaskEvent::create([
                'task_id' => $task->id,
                'actor_user_id' => $actorUserId,
                'event_type' => $e['event_type'],
                'from_status' => $e['from'] ?? null,
                'to_status' => $e['to'] ?? null,
                'note' => $e['note'] ?? null,
                'meta' => $e['meta'] ?? null,
                'created_at' => $e['at'] ?? now(),
                'updated_at' => $e['at'] ?? now(),
            ]);
        }
    }
}
