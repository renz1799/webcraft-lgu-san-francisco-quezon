<?php

namespace App\Builders\Contracts\Tasks;

use App\Models\Task;

interface TaskDatatableRowBuilderInterface
{
    public function build(Task $task, array $context = []): array;
}
