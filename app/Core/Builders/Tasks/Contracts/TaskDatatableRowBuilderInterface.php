<?php

namespace App\Core\Builders\Tasks\Contracts;

use App\Core\Models\Tasks\Task;

interface TaskDatatableRowBuilderInterface
{
    public function build(Task $task, array $context = []): array;
}
