<?php

namespace Tests\Feature;

use App\Core\Builders\Tasks\TaskTimelineContextMetaBuilder;
use App\Core\Models\Tasks\Task;
use Tests\TestCase;

class TaskTimelineContextMetaBuilderTest extends TestCase
{
    public function test_build_returns_change_flags_and_meta_for_timeline_updates(): void
    {
        $task = new Task([
            'id' => 'task-1',
            'title' => 'Original Title',
            'description' => 'Original Description',
            'type' => 'review',
            'assigned_to_user_id' => 'user-1',
            'data' => [
                'subject_url' => '/subjects/1',
                'meta' => ['priority' => 'normal'],
            ],
        ]);

        $builder = new TaskTimelineContextMetaBuilder();

        $payload = $builder->build(
            task: $task,
            previousData: [
                'subject_url' => '/subjects/1',
                'meta' => ['priority' => 'normal'],
            ],
            mergedData: [
                'subject_url' => '/subjects/2',
                'meta' => ['priority' => 'high'],
            ],
            assignmentMode: 'set',
            targetAssignee: 'user-2',
            title: 'Updated Title',
            description: 'Updated Description',
            type: 'approval',
            subjectType: 'inspection',
            subjectId: 'subject-1',
        );

        $this->assertTrue($payload['has_changes']);
        $this->assertSame(['meta.priority', 'subject_url'], $payload['meta']['changed_data_keys']);
        $this->assertTrue($payload['meta']['assignee_changed']);
        $this->assertSame('user-1', $payload['meta']['from_assigned_to_user_id']);
        $this->assertSame('user-2', $payload['meta']['to_assigned_to_user_id']);
        $this->assertTrue($payload['meta']['title_changed']);
        $this->assertTrue($payload['meta']['description_changed']);
        $this->assertTrue($payload['meta']['type_changed']);
        $this->assertSame('inspection', $payload['meta']['subject_type']);
        $this->assertSame('subject-1', $payload['meta']['subject_id']);
    }
}
