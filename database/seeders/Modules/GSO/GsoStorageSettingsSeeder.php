<?php

namespace Database\Seeders\Modules\GSO;

use App\Core\Models\AppSetting;
use App\Core\Models\Module;
use Illuminate\Database\Seeder;

class GsoStorageSettingsSeeder extends Seeder
{
    private const MODULE_CODE = 'GSO';

    private const SETTINGS_KEY = 'storage.google_drive';

    public function run(): void
    {
        $module = Module::query()
            ->where('code', self::MODULE_CODE)
            ->first();

        if (! $module) {
            return;
        }

        $existing = AppSetting::query()
            ->where('module_id', (string) $module->id)
            ->where('key', self::SETTINGS_KEY)
            ->first();

        $stored = is_array($existing?->value) ? $existing->value : [];
        $merged = array_filter([
            'signed_documents_root_folder_id' => $this->firstDefinedValue(
                $stored,
                ['signed_documents_root_folder_id', 'air_files_folder_id'],
                [config('gso.storage.air_files_folder_id')],
            ),
            'air_inspections_root_folder_id' => $this->firstDefinedValue(
                $stored,
                ['air_inspections_root_folder_id', 'inspection_photos_folder_id', 'air_unit_files_folder_id'],
                [
                    config('gso.storage.inspection_photos_folder_id'),
                    config('gso.storage.air_unit_files_folder_id'),
                ],
            ),
            'inventory_items_root_folder_id' => $this->firstDefinedValue(
                $stored,
                ['inventory_items_root_folder_id', 'inventory_files_folder_id'],
                [config('gso.storage.inventory_files_folder_id')],
            ),
        ], fn (?string $value): bool => $value !== null);

        if ($merged === []) {
            return;
        }

        AppSetting::updateOrCreate(
            [
                'module_id' => (string) $module->id,
                'key' => self::SETTINGS_KEY,
            ],
            ['value' => $merged],
        );
    }

    /**
     * @param  array<string, mixed>  $stored
     * @param  array<int, string>  $keys
     * @param  array<int, mixed>  $fallbacks
     */
    private function firstDefinedValue(array $stored, array $keys, array $fallbacks): ?string
    {
        foreach ($keys as $key) {
            $storedValue = $this->normalizeNullableString($stored[$key] ?? null);

            if ($storedValue !== null) {
                return $storedValue;
            }
        }

        foreach ($fallbacks as $fallback) {
            $fallbackValue = $this->normalizeNullableString($fallback);

            if ($fallbackValue !== null) {
                return $fallbackValue;
            }
        }

        return null;
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
