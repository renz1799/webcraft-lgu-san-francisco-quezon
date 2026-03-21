<?php

namespace App\Modules\Tasks\Builders\Contracts;

use App\Modules\Tasks\Models\Task;

interface TaskNotificationPayloadBuilderInterface
{
    public function buildAssigned(Task $task): array;

    public function buildStatusChanged(Task $task, string $fromStatus, string $toStatus): array;

    public function buildReassigned(Task $task): array;

    public function buildClaimed(Task $task): array;
}
