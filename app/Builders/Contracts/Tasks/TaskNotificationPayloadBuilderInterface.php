<?php

namespace App\Builders\Contracts\Tasks;

use App\Models\Task;

interface TaskNotificationPayloadBuilderInterface
{
    public function buildAssigned(Task $task): array;

    public function buildStatusChanged(Task $task, string $fromStatus, string $toStatus): array;

    public function buildReassigned(Task $task): array;

    public function buildClaimed(Task $task): array;
}
