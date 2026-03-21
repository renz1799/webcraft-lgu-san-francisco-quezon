<?php

namespace App\Core\Services\Contracts\GoogleDrive;

interface GoogleDriveConnectionServiceInterface
{
    public function isConnected(): bool;

    public function getAuthUrl(): string;

    public function handleCallback(string $code, ?string $connectedByUserId = null): void;

    public function disconnect(): void;
}
