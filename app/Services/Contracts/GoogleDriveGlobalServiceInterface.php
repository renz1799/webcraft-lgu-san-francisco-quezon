<?php

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

interface GoogleDriveGlobalServiceInterface
{
    public function isConnected(): bool;

    public function getAuthUrl(): string;

    public function handleCallback(string $code): void;

    public function upload(UploadedFile $file, ?string $name = null, bool $makePublic = false): array;

    public function disconnect(): void;
    
    public function download(string $fileId): array;

}
