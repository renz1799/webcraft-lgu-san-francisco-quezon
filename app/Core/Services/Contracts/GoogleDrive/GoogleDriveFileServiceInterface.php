<?php

namespace App\Core\Services\Contracts\GoogleDrive;

use Illuminate\Http\UploadedFile;

interface GoogleDriveFileServiceInterface
{
    public function upload(
        UploadedFile $file,
        ?string $name = null,
        bool $makePublic = false,
        ?string $folderId = null,
    ): array;

    public function uploadFromPath(
        string $path,
        ?string $name = null,
        ?string $mimeType = null,
        bool $makePublic = false,
        ?string $folderId = null,
    ): array;

    public function copyFile(
        string $sourceFileId,
        ?string $newName = null,
        ?string $targetFolderId = null,
    ): array;

    public function replaceFileInFolder(
        string $path,
        string $name,
        string $folderId,
        ?string $mimeType = null,
        bool $makePublic = false,
    ): array;

    public function deleteFile(string $fileId): void;

    public function download(string $fileId): array;
}
