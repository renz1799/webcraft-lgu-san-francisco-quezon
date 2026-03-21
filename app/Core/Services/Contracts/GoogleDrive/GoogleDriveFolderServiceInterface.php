<?php

namespace App\Core\Services\Contracts\GoogleDrive;

interface GoogleDriveFolderServiceInterface
{
    public function sanitizeFolderName(string $value): string;

    public function ensureFolder(string $name, string $parentId): array;
}
