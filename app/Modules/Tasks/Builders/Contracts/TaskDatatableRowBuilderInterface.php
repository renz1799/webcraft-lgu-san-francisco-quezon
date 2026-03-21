<?php

namespace App\Modules\Tasks\Builders\Contracts;

use App\Modules\Tasks\Models\Task;

interface TaskDatatableRowBuilderInterface
{
    public function build(Task $task, array $context = []): array;
}
