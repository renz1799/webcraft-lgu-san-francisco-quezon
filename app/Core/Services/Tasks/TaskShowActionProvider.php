<?php

namespace App\Core\Services\Tasks;

use App\Core\Models\Tasks\Task;
use App\Core\Services\Tasks\Contracts\TaskShowActionProviderInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class TaskShowActionProvider implements TaskShowActionProviderInterface
{
    public function getHeaderActions(?Authenticatable $actor, Task $task): array
    {
        return [];
    }
}
