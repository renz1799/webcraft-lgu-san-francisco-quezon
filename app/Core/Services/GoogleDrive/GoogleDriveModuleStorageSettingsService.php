<?php

namespace App\Core\Services\GoogleDrive;

use App\Core\Models\AppSetting;
use App\Core\Models\Module;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveModuleStorageSettingsServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveSettingsProviderInterface;
use Illuminate\Support\Collection;
use RuntimeException;

class GoogleDriveModuleStorageSettingsService implements GoogleDriveModuleStorageSettingsServiceInterface
{
    public function __construct(
        private readonly GoogleDriveSettingsProviderInterface $settingsProvider,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function contexts(): Collection
    {
        $profiles = collect((array) config('google-drive-storage.modules', []));

        if ($profiles->isEmpty()) {
            return collect();
        }

        $modules = Module::query()
            ->where('is_active', true)
            ->whereIn('code', $profiles->keys()->all())
            ->orderBy('name')
            ->get()
            ->keyBy(fn (Module $module) => strtoupper((string) $module->code));

        return $profiles
            ->map(function (array $profile, string $moduleCode) use ($modules): ?array {
                /** @var Module|null $module */
                $module = $modules->get(strtoupper($moduleCode));

                if (! $module) {
                    return null;
                }

                $settingKey = (string) ($profile['setting_key'] ?? 'storage.google_drive');
                $storedValues = $this->storedValues((string) $module->id, $settingKey);

                return [
                    'module' => $module,
                    'setting_key' => $settingKey,
                    'title' => (string) ($profile['title'] ?? ($module->name . ' Storage Roots')),
                    'description' => (string) ($profile['description'] ?? ''),
                    'notes' => collect((array) ($profile['notes'] ?? []))
                        ->map(fn (mixed $note): string => trim((string) $note))
                        ->filter()
                        ->values()
                        ->all(),
                    'fields' => collect((array) ($profile['fields'] ?? []))
                        ->map(function (array $field, string $fieldKey) use ($storedValues): array {
                            $storedState = $this->resolveStoredState($storedValues, $fieldKey, $field);
                            $fallbackState = $this->resolveFallbackState($field);
                            $storedValue = $storedState['value'];
                            $fallbackValue = $fallbackState['value'];
                            $effectiveValue = $storedValue ?? $fallbackValue;
                            $source = 'Not configured';

                            if ($storedValue !== null) {
                                $source = $storedState['source'];
                            } elseif ($fallbackValue !== null) {
                                $source = $fallbackState['source'];
                            }

                            return [
                                'key' => $fieldKey,
                                'label' => (string) ($field['label'] ?? $this->humanizeKey($fieldKey)),
                                'help' => $this->normalizeNullableString($field['help'] ?? null),
                                'stored_value' => $storedValue,
                                'fallback_value' => $fallbackValue,
                                'effective_value' => $effectiveValue,
                                'source' => $source,
                                'examples' => collect((array) ($field['examples'] ?? []))
                                    ->map(fn (mixed $example): string => trim((string) $example))
                                    ->filter()
                                    ->values()
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
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function updateModuleSettings(string $moduleId, array $values): array
    {
        $module = Module::query()
            ->whereKey($moduleId)
            ->where('is_active', true)
            ->firstOrFail();

        $moduleCode = strtoupper((string) $module->code);
        $profiles = (array) config('google-drive-storage.modules', []);
        $profile = (array) ($profiles[$moduleCode] ?? []);

        if ($profile === []) {
            throw new RuntimeException("Google Drive storage settings are not configured for {$moduleCode}.");
        }

        $settingKey = (string) ($profile['setting_key'] ?? 'storage.google_drive');
        $fieldDefinitions = collect((array) ($profile['fields'] ?? []));
        $current = $this->storedValues((string) $module->id, $settingKey);

        foreach ($fieldDefinitions as $fieldKey => $field) {
            $storedKeys = $this->storedKeysForField($fieldKey, (array) $field);

            if (! array_key_exists($fieldKey, $values)) {
                continue;
            }

            $normalized = $this->normalizeNullableString($values[$fieldKey] ?? null);

            foreach ($storedKeys as $storedKey) {
                unset($current[$storedKey]);
            }

            if ($normalized === null) {
                continue;
            }

            $current[$fieldKey] = $normalized;
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
     * @param  array<string, mixed>  $field
     * @return array{value:?string,source:string}
     */
    private function resolveStoredState(array $storedValues, string $fieldKey, array $field): array
    {
        foreach ($this->storedKeysForField($fieldKey, $field) as $storedKey) {
            $storedValue = $this->normalizeNullableString($storedValues[$storedKey] ?? null);

            if ($storedValue === null) {
                continue;
            }

            return [
                'value' => $storedValue,
                'source' => $storedKey === $fieldKey
                    ? 'App settings'
                    : "App settings (legacy key: {$storedKey})",
            ];
        }

        return [
            'value' => null,
            'source' => 'Not configured',
        ];
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array{value:?string,source:string}
     */
    private function resolveFallbackState(array $field): array
    {
        foreach ($this->fallbackConfigKeysForField($field) as $configKey) {
            $fallbackValue = $this->normalizeNullableString(config($configKey));

            if ($fallbackValue === null) {
                continue;
            }

            return [
                'value' => $fallbackValue,
                'source' => "Config fallback ({$configKey})",
            ];
        }

        $defaultFolderId = $this->settingsProvider->defaultFolderId();

        return [
            'value' => $defaultFolderId,
            'source' => $defaultFolderId !== null ? 'Global Drive default' : 'Not configured',
        ];
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<int, string>
     */
    private function storedKeysForField(string $fieldKey, array $field): array
    {
        return collect((array) ($field['stored_keys'] ?? [$fieldKey]))
            ->prepend($fieldKey)
            ->map(fn (mixed $key): string => trim((string) $key))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<int, string>
     */
    private function fallbackConfigKeysForField(array $field): array
    {
        return collect((array) ($field['fallback_config_keys'] ?? []))
            ->prepend($field['fallback_config_key'] ?? null)
            ->map(fn (mixed $key): string => trim((string) $key))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function humanizeKey(string $key): string
    {
        return str($key)
            ->replace('_id', ' ID')
            ->replace('_', ' ')
            ->title()
            ->value();
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
