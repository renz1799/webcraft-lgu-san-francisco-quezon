<?php

namespace App\Core\Services\Contracts\GoogleDrive;

interface GoogleDriveConnectionServiceInterface
{
    public function isConnected(): bool;

    public function isConnectedFor(string $moduleId, string $departmentId): bool;

    public function getAuthUrl(): string;

    public function getAuthUrlFor(string $moduleId, string $departmentId): string;

    public function handleCallback(string $code, ?string $connectedByUserId = null): void;

    public function handleCallbackFor(string $moduleId, string $departmentId, string $code, ?string $connectedByUserId = null): void;

    public function disconnect(): void;

    public function disconnectFor(string $moduleId, string $departmentId): void;
}
