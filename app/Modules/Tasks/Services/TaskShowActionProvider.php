<?php

namespace App\Modules\Tasks\Services;

use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Services\Contracts\TaskShowActionProviderInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class TaskShowActionProvider implements TaskShowActionProviderInterface
{
    public function getHeaderActions(?Authenticatable $actor, Task $task): array
    {
        return [];
    }
}
