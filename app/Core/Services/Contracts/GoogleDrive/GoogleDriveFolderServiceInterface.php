<?php

namespace App\Core\Services\Contracts\GoogleDrive;

interface GoogleDriveFolderServiceInterface
{
    public function sanitizeFolderName(string $value): string;

    /**
     * @return array<string, mixed>|null
     */
    public function findFolder(string $name, string $parentId): ?array;

    public function ensureFolder(string $name, string $parentId): array;
}
