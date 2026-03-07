<?php

namespace App\Services\Contracts;

use App\Models\Task;

interface TaskServiceInterface
{
    public function createAndAssign(
        string $actorUserId,
        string $assigneeUserId,
        string $title,
        ?string $description = null,
        ?string $type = null,
        ?string $subjectType = null,
        ?string $subjectId = null,
        array $data = []
    ): Task;

    public function changeStatus(
        string $actorUserId,
        string $taskId,
        string $toStatus,
        ?string $note = null
    ): Task;

    public function addComment(
        string $actorUserId,
        string $taskId,
        string $note
    ): void;

    public function reassign(
        string $actorUserId,
        string $taskId,
        string $newAssigneeUserId,
        ?string $note = null
    ): Task;

    public function claim(string $actorUserId, string $taskId, ?string $note = null): Task;

    public function archive(string $actorUserId, string $taskId): void;

    public function restore(string $actorUserId, string $taskId): bool;

    public function datatable(array $params): array;
}
