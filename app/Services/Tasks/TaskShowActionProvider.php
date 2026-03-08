<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Services\Contracts\TaskShowActionProviderInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class TaskShowActionProvider implements TaskShowActionProviderInterface
{
    public function getHeaderActions(?Authenticatable $actor, Task $task): array
    {
        return [];
    }
}
