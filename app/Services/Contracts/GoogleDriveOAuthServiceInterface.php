<?php

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

interface GoogleDriveOAuthServiceInterface
{
    public function getAuthUrl(string $userId): string;

    public function handleCallback(string $userId, string $code): void;

    public function upload(string $userId, UploadedFile $file, ?string $name = null, bool $makePublic = false): array;

    public function download(string $userId, string $fileId): array;
}
