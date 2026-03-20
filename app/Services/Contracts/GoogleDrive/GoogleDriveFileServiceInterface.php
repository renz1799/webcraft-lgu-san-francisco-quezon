<?php

namespace App\Services\Contracts\GoogleDrive;

use Illuminate\Http\UploadedFile;

interface GoogleDriveFileServiceInterface
{
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

    public function download(string $fileId): array;
}
