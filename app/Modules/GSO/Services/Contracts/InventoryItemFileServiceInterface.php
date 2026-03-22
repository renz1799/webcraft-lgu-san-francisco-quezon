<?php

namespace App\Modules\GSO\Services\Contracts;

interface InventoryItemFileServiceInterface
{
    /**
     * @return array{inventory_item: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    public function listForInventoryItem(string $inventoryItemId): array;

    /**
     * @param  array<int, mixed>  $files
     * @return array{inventory_item: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    public function upload(string $actorUserId, string $inventoryItemId, array $files, ?string $type = null): array;

    /**
     * @return array{inventory_item: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    public function delete(string $actorUserId, string $inventoryItemId, string $fileId): array;

    /**
     * @return array{inventory_item: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    public function importInspectionPhotos(string $actorUserId, string $inventoryItemId, string $inspectionId): array;

    /**
     * @return array{name: string, mime: string, bytes: string}
     */
    public function preview(string $inventoryItemId, string $fileId): array;
}
