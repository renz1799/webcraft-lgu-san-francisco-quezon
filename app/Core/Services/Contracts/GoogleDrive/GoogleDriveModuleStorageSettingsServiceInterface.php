<?php

namespace App\Core\Services\Contracts\GoogleDrive;

use Illuminate\Support\Collection;

interface GoogleDriveModuleStorageSettingsServiceInterface
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function contexts(): Collection;

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function updateModuleSettings(string $moduleId, array $values): array;
}
