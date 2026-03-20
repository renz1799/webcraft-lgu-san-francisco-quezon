<?php

namespace App\Services\Contracts\Tasks;

use App\Models\Task;
use Illuminate\Contracts\Auth\Authenticatable;

interface TaskShowActionProviderInterface
{
    /**
     * Return task-show header actions for project-specific extensions.
     *
     * Action shape:
     * - type: "link"|"button" (default: "link")
     * - label: string (required)
     * - href: string (for links)
     * - classes: string (optional)
     * - attributes: array<string, scalar|null> (optional)
     */
    public function getHeaderActions(?Authenticatable $actor, Task $task): array;
}
