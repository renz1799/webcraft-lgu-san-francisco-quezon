<?php

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

interface GoogleDriveServiceInterface
{
    /**
     * Upload any file (image/pdf/etc) to Drive folder.
     * Returns normalized metadata for storage in DB.
     */
    public function upload(
        UploadedFile $file,
        ?string $name = null,
        bool $makePublic = false
    ): array;

    /**
     * Convenience: upload an image (validates mime intent only).
     */
    public function uploadImage(
        UploadedFile $file,
        ?string $name = null,
        bool $makePublic = false
    ): array;

    /**
     * Convenience: upload a PDF.
     */
    public function uploadPdf(
        UploadedFile $file,
        ?string $name = null,
        bool $makePublic = false
    ): array;

    /**
     * Delete a Drive file by fileId.
     */
    public function delete(string $fileId): void;

    /**
     * Fetch Drive file metadata (optional).
     */
    public function get(string $fileId): array;
}
