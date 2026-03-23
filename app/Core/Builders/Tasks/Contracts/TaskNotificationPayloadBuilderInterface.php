<?php

namespace App\Core\Builders\Tasks\Contracts;

use App\Core\Models\Tasks\Task;

interface TaskNotificationPayloadBuilderInterface
{
    public function buildAssigned(Task $task): array;

    public function buildStatusChanged(Task $task, string $fromStatus, string $toStatus): array;

    public function buildReassigned(Task $task): array;

    public function buildClaimed(Task $task): array;
}
