<?php

namespace App\Core\Services\Notifications;

use App\Core\Models\AppSetting;
use App\Core\Models\Module;
use App\Core\Models\Role;
use App\Core\Services\Contracts\Notifications\WorkflowNotificationSettingsServiceInterface;
use Illuminate\Support\Collection;
use RuntimeException;

class WorkflowNotificationSettingsService implements WorkflowNotificationSettingsServiceInterface
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function contexts(): Collection
    {
        $profiles = collect((array) config('workflow-notifications.modules', []));

        if ($profiles->isEmpty()) {
            return collect();
        }

        $modules = Module::query()
            ->where('is_active', true)
            ->whereIn('code', $profiles->keys()->all())
            ->orderBy('name')
            ->get()
            ->keyBy(fn (Module $module) => strtoupper((string) $module->code));

        $roleLookup = Role::query()
            ->select(['module_id', 'name'])
            ->whereIn('module_id', $modules->pluck('id')->all())
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy('module_id')
            ->map(fn (Collection $roles): array => $roles
                ->pluck('name')
                ->map(fn ($name): string => trim((string) $name))
                ->filter()
                ->unique()
                ->values()
                ->all());

        return $profiles
            ->map(function (array $profile, string $moduleCode) use ($modules, $roleLookup): ?array {
                /** @var Module|null $module */
                $module = $modules->get(strtoupper($moduleCode));

                if (! $module) {
                    return null;
                }

                $settingKey = (string) ($profile['setting_key'] ?? 'workflow.notifications');
                $storedValues = $this->storedValues((string) $module->id, $settingKey);
                $moduleRoleNames = $roleLookup->get((string) $module->id, []);

                return [
                    'module' => $module,
                    'setting_key' => $settingKey,
                    'title' => (string) ($profile['title'] ?? ($module->name . ' Workflow Notification Rules')),
                    'description' => (string) ($profile['description'] ?? ''),
                    'notes' => collect((array) ($profile['notes'] ?? []))
                        ->map(fn (mixed $note): string => trim((string) $note))
                        ->filter()
                        ->values()
                        ->all(),
                    'events' => collect((array) ($profile['events'] ?? []))
                        ->map(function (array $event, string $eventKey) use ($storedValues, $moduleRoleNames): array {
                            $defaultRoles = $this->normalizeRoleNames($event['default_roles'] ?? []);
                            $storedRoles = $this->storedEventSettings($storedValues, $eventKey);
                            $availableRoles = collect(array_merge(
                                $moduleRoleNames,
                                $defaultRoles,
                                $storedRoles['roles']
                            ))
                                ->map(fn (mixed $role): string => trim((string) $role))
                                ->filter()
                                ->unique()
                                ->values()
                                ->all();

                            return [
                                'key' => $eventKey,
                                'label' => (string) ($event['label'] ?? $this->humanizeKey($eventKey)),
                                'description' => trim((string) ($event['description'] ?? '')),
                                'default_roles' => $defaultRoles,
                                'stored_roles' => $storedRoles['configured'] ? $storedRoles['roles'] : [],
                                'effective_roles' => $storedRoles['configured'] ? $storedRoles['roles'] : $defaultRoles,
                                'default_message_template' => trim((string) ($event['message_template'] ?? '')),
                                'stored_message_template' => $storedRoles['message_template_configured']
                                    ? $storedRoles['message_template']
                                    : '',
                                'effective_message_template' => $storedRoles['message_template_configured']
                                    ? $storedRoles['message_template']
                                    : trim((string) ($event['message_template'] ?? '')),
                                'source' => $storedRoles['configured']
                                    ? ($storedRoles['roles'] === [] ? 'App settings (disabled)' : 'App settings')
                                    : 'Default rule',
                                'available_roles' => $availableRoles,
                                'placeholders' => collect((array) ($event['placeholders'] ?? []))
                                    ->mapWithKeys(fn (mixed $description, mixed $placeholder): array => [
                                        trim((string) $placeholder) => trim((string) $description),
                                    ])
                                    ->filter(fn (string $description, string $placeholder): bool => $placeholder !== '' && $description !== '')
                                    ->all(),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @param  array<string, mixed>  $events
     * @return array<string, mixed>
     */
    public function updateModuleSettings(string $moduleId, array $events): array
    {
        $module = Module::query()
            ->whereKey($moduleId)
            ->where('is_active', true)
            ->firstOrFail();

        $profile = $this->profileForModuleCode((string) $module->code);
        $settingKey = (string) ($profile['setting_key'] ?? 'workflow.notifications');
        $current = $this->storedValues((string) $module->id, $settingKey);
        $availableRoles = $this->availableRoleNamesForModule((string) $module->id);

        foreach ((array) ($profile['events'] ?? []) as $eventKey => $eventProfile) {
            if (! array_key_exists($eventKey, $events)) {
                continue;
            }

            $payload = $events[$eventKey];
            $requestedRoles = is_array($payload)
                ? ($payload['roles'] ?? [])
                : $payload;
            $requestedMessageTemplate = is_array($payload)
                ? trim((string) ($payload['message_template'] ?? ''))
                : '';

            $normalizedRoles = array_values(array_intersect(
                $this->normalizeRoleNames($requestedRoles),
                $availableRoles
            ));

            $defaultRoles = $this->normalizeRoleNames($eventProfile['default_roles'] ?? []);
            $defaultMessageTemplate = trim((string) ($eventProfile['message_template'] ?? ''));

            $eventSettings = [];

            if (! $this->sameRoleSet($normalizedRoles, $defaultRoles)) {
                $eventSettings['roles'] = $normalizedRoles;
            }

            if ($requestedMessageTemplate !== '' && $requestedMessageTemplate !== $defaultMessageTemplate) {
                $eventSettings['message_template'] = $requestedMessageTemplate;
            }

            if ($eventSettings === []) {
                unset($current[$eventKey]);
                continue;
            }

            $current[$eventKey] = $eventSettings;
        }

        if ($current === []) {
            AppSetting::query()
                ->where('module_id', (string) $module->id)
                ->where('key', $settingKey)
                ->delete();

            return [];
        }

        AppSetting::updateOrCreate(
            [
                'module_id' => (string) $module->id,
                'key' => $settingKey,
            ],
            ['value' => $current],
        );

        return $current;
    }

    /**
     * @return array<int, string>
     */
    public function rolesForEvent(string $moduleCode, string $eventKey): array
    {
        $profile = $this->profileForModuleCode($moduleCode, false);
        $eventProfile = (array) ($profile['events'][$eventKey] ?? []);
        $defaultRoles = $this->normalizeRoleNames(($eventProfile['default_roles'] ?? []));

        if ($profile === []) {
            return $defaultRoles;
        }

        $module = Module::query()
            ->where('is_active', true)
            ->where('code', strtoupper(trim($moduleCode)))
            ->first();

        if (! $module) {
            return $defaultRoles;
        }

        $storedValues = $this->storedValues(
            (string) $module->id,
            (string) ($profile['setting_key'] ?? 'workflow.notifications')
        );

        $storedEvent = $this->storedEventSettings($storedValues, $eventKey);

        if (! $storedEvent['configured_roles']) {
            return $defaultRoles;
        }

        $availableRoles = $this->availableRoleNamesForModule((string) $module->id);

        return array_values(array_intersect(
            $storedEvent['roles'],
            $availableRoles
        ));
    }

    public function messageTemplateForEvent(string $moduleCode, string $eventKey): string
    {
        $profile = $this->profileForModuleCode($moduleCode, false);
        $eventProfile = (array) ($profile['events'][$eventKey] ?? []);
        $defaultMessageTemplate = trim((string) ($eventProfile['message_template'] ?? ''));

        if ($profile === []) {
            return $defaultMessageTemplate;
        }

        $module = Module::query()
            ->where('is_active', true)
            ->where('code', strtoupper(trim($moduleCode)))
            ->first();

        if (! $module) {
            return $defaultMessageTemplate;
        }

        $storedValues = $this->storedValues(
            (string) $module->id,
            (string) ($profile['setting_key'] ?? 'workflow.notifications')
        );

        $storedEvent = $this->storedEventSettings($storedValues, $eventKey);

        if (! $storedEvent['message_template_configured']) {
            return $defaultMessageTemplate;
        }

        return $storedEvent['message_template'] !== ''
            ? $storedEvent['message_template']
            : $defaultMessageTemplate;
    }

    /**
     * @return array<string, mixed>
     */
    private function storedValues(string $moduleId, string $settingKey): array
    {
        $row = AppSetting::query()
            ->where('module_id', $moduleId)
            ->where('key', $settingKey)
            ->first();

        return is_array($row?->value)
            ? $row->value
            : [];
    }

    /**
     * @param  array<string, mixed>  $storedValues
     * @return array{configured:bool,roles:array<int, string>,configured_roles:bool,message_template:string,message_template_configured:bool}
     */
    private function storedEventSettings(array $storedValues, string $eventKey): array
    {
        if (! array_key_exists($eventKey, $storedValues) || ! is_array($storedValues[$eventKey])) {
            return [
                'configured' => false,
                'roles' => [],
                'configured_roles' => false,
                'message_template' => '',
                'message_template_configured' => false,
            ];
        }

        $payload = $storedValues[$eventKey];
        $isAssociative = array_keys($payload) !== range(0, count($payload) - 1);
        $roles = $isAssociative
            ? $this->normalizeRoleNames($payload['roles'] ?? [])
            : $this->normalizeRoleNames($payload);
        $messageTemplateConfigured = $isAssociative && array_key_exists('message_template', $payload);
        $messageTemplate = $messageTemplateConfigured
            ? trim((string) ($payload['message_template'] ?? ''))
            : '';

        return [
            'configured' => true,
            'roles' => $roles,
            'configured_roles' => ! $isAssociative || array_key_exists('roles', $payload),
            'message_template' => $messageTemplate,
            'message_template_configured' => $messageTemplateConfigured,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function profileForModuleCode(string $moduleCode, bool $failIfMissing = true): array
    {
        $profiles = (array) config('workflow-notifications.modules', []);
        $profile = (array) ($profiles[strtoupper(trim($moduleCode))] ?? []);

        if ($profile === [] && $failIfMissing) {
            throw new RuntimeException("Workflow notification settings are not configured for {$moduleCode}.");
        }

        return $profile;
    }

    /**
     * @return array<int, string>
     */
    private function availableRoleNamesForModule(string $moduleId): array
    {
        return Role::query()
            ->where('module_id', $moduleId)
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name): string => trim((string) $name))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function normalizeRoleNames(mixed $roles): array
    {
        return collect(is_array($roles) ? $roles : [$roles])
            ->map(fn (mixed $role): string => trim((string) $role))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $left
     * @param  array<int, string>  $right
     */
    private function sameRoleSet(array $left, array $right): bool
    {
        sort($left);
        sort($right);

        return $left === $right;
    }

    private function humanizeKey(string $key): string
    {
        return str($key)
            ->replace('.', ' / ')
            ->replace('_', ' ')
            ->title()
            ->value();
    }
}
