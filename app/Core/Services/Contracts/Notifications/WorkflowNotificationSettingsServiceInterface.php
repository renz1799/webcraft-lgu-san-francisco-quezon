<?php

namespace App\Core\Services\Contracts\Notifications;

use Illuminate\Support\Collection;

interface WorkflowNotificationSettingsServiceInterface
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function contexts(): Collection;

    /**
     * @param  array<string, mixed>  $events
     * @return array<string, mixed>
     */
    public function updateModuleSettings(string $moduleId, array $events): array;

    /**
     * @return array<int, string>
     */
    public function rolesForEvent(string $moduleCode, string $eventKey): array;

    public function messageTemplateForEvent(string $moduleCode, string $eventKey): string;
}
