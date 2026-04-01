<?php

namespace App\Modules\GSO\Services;

use App\Core\Models\AppSetting;
use App\Core\Models\Module;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveSettingsProviderInterface;
use App\Modules\GSO\Services\Contracts\GsoStorageSettingsServiceInterface;

class GsoStorageSettingsService implements GsoStorageSettingsServiceInterface
{
    private const MODULE_CODE = 'GSO';

    private const SETTINGS_KEY = 'storage.google_drive';

    private const SIGNED_DOCUMENTS_ROOT_KEY = 'signed_documents_root_folder_id';

    private const AIR_INSPECTIONS_ROOT_KEY = 'air_inspections_root_folder_id';

    private const INVENTORY_ITEMS_ROOT_KEY = 'inventory_items_root_folder_id';

    /**
     * @var array<string, mixed>|null
     */
    private ?array $googleDriveSettings = null;

    public function __construct(
        private readonly GoogleDriveSettingsProviderInterface $googleDriveSettingsProvider,
    ) {}

    public function googleDriveRoots(): array
    {
        return array_filter([
            self::SIGNED_DOCUMENTS_ROOT_KEY => $this->signedDocumentsRootFolderId(),
            self::AIR_INSPECTIONS_ROOT_KEY => $this->airInspectionsRootFolderId(),
            self::INVENTORY_ITEMS_ROOT_KEY => $this->inventoryItemsRootFolderId(),
        ], fn (?string $value): bool => $value !== null);
    }

    public function inspectionPhotosFolderId(): ?string
    {
        return $this->resolveFolderId(
            [
                self::AIR_INSPECTIONS_ROOT_KEY,
                'inspection_photos_folder_id',
            ],
            [
                config('gso.storage.inspection_photos_folder_id'),
                config('gso.storage.air_unit_files_folder_id'),
            ],
        );
    }

    public function airUnitFilesFolderId(): ?string
    {
        return $this->resolveFolderId(
            [
                self::AIR_INSPECTIONS_ROOT_KEY,
                'air_unit_files_folder_id',
            ],
            [
                config('gso.storage.air_unit_files_folder_id'),
                config('gso.storage.inspection_photos_folder_id'),
            ],
        );
    }

    public function airFilesFolderId(): ?string
    {
        return $this->resolveFolderId(
            [
                self::SIGNED_DOCUMENTS_ROOT_KEY,
                'air_files_folder_id',
            ],
            [
                config('gso.storage.air_files_folder_id'),
            ],
        );
    }

    public function inventoryFilesFolderId(): ?string
    {
        return $this->resolveFolderId(
            [
                self::INVENTORY_ITEMS_ROOT_KEY,
                'inventory_files_folder_id',
            ],
            [
                config('gso.storage.inventory_files_folder_id'),
            ],
        );
    }

    public function signedDocumentsRootFolderId(): ?string
    {
        return $this->resolveFolderId(
            [
                self::SIGNED_DOCUMENTS_ROOT_KEY,
                'air_files_folder_id',
            ],
            [
                config('gso.storage.air_files_folder_id'),
            ],
        );
    }

    private function airInspectionsRootFolderId(): ?string
    {
        return $this->resolveFolderId(
            [
                self::AIR_INSPECTIONS_ROOT_KEY,
                'inspection_photos_folder_id',
                'air_unit_files_folder_id',
            ],
            [
                config('gso.storage.inspection_photos_folder_id'),
                config('gso.storage.air_unit_files_folder_id'),
            ],
        );
    }

    private function inventoryItemsRootFolderId(): ?string
    {
        return $this->resolveFolderId(
            [
                self::INVENTORY_ITEMS_ROOT_KEY,
                'inventory_files_folder_id',
            ],
            [
                config('gso.storage.inventory_files_folder_id'),
            ],
        );
    }

    /**
     * @param  array<int, string>  $keys
     * @param  array<int, mixed>  $fallbacks
     */
    private function resolveFolderId(array $keys, array $fallbacks): ?string
    {
        foreach ($keys as $key) {
            $configured = $this->normalizeNullableString(data_get($this->googleDriveSettings(), $key));

            if ($configured !== null) {
                return $configured;
            }
        }

        foreach ($fallbacks as $fallback) {
            $normalized = $this->normalizeNullableString($fallback);

            if ($normalized !== null) {
                return $normalized;
            }
        }

        return $this->googleDriveSettingsProvider->defaultFolderId();
    }

    /**
     * @return array<string, mixed>
     */
    private function googleDriveSettings(): array
    {
        if ($this->googleDriveSettings !== null) {
            return $this->googleDriveSettings;
        }

        $moduleId = Module::query()
            ->where('code', self::MODULE_CODE)
            ->value('id');

        if (! is_string($moduleId) || trim($moduleId) === '') {
            return $this->googleDriveSettings = [];
        }

        $row = AppSetting::query()
            ->where('module_id', $moduleId)
            ->where('key', self::SETTINGS_KEY)
            ->first();

        return $this->googleDriveSettings = is_array($row?->value)
            ? $row->value
            : [];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
