<?php

namespace App\Modules\GSO\Services\Contracts;

interface GsoStorageSettingsServiceInterface
{
    /**
     * @return array<string, string>
     */
    public function googleDriveRoots(): array;

    public function signedDocumentsRootFolderId(): ?string;

    public function inspectionPhotosFolderId(): ?string;

    public function airUnitFilesFolderId(): ?string;

    public function airFilesFolderId(): ?string;

    public function inventoryFilesFolderId(): ?string;
}
