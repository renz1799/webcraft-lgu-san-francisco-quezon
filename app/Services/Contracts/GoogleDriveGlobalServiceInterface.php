<?php

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

interface GoogleDriveGlobalServiceInterface
{
    public function isConnected(): bool;

    public function getAuthUrl(): string;

    public function handleCallback(string $code): void;

    public function sanitizeFolderName(string $value): string;

    public function ensureFolder(string $name, string $parentId): array;

    public function upload(
        UploadedFile $file,
        ?string $name = null,
        bool $makePublic = false,
        ?string $folderId = null,
    ): array;

    public function copyFile(
        string $sourceFileId,
        ?string $newName = null,
        ?string $targetFolderId = null,
    ): array;

    public function deleteFile(string $fileId): void;

    public function disconnect(): void;

    public function download(string $fileId): array;
}