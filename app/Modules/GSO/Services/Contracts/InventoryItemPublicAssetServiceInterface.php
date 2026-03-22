<?php

namespace App\Modules\GSO\Services\Contracts;

interface InventoryItemPublicAssetServiceInterface
{
    /**
     * @return array{view: string, data: array<string, mixed>}
     */
    public function getPublicAssetPagePayload(string $code): array;

    /**
     * @return array{name: string, mime: string, bytes: string}
     */
    public function streamPublicAssetFile(string $code, string $fileId): array;
}
