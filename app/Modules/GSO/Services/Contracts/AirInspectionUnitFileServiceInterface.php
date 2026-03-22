<?php

namespace App\Modules\GSO\Services\Contracts;

interface AirInspectionUnitFileServiceInterface
{
    /**
     * @return array{air: array<string, mixed>, air_item: array<string, mixed>, unit: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    public function listForUnit(string $airId, string $airItemId, string $unitId): array;

    /**
     * @param  array<int, mixed>  $files
     * @return array{air: array<string, mixed>, air_item: array<string, mixed>, unit: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    public function upload(string $actorUserId, string $airId, string $airItemId, string $unitId, array $files, ?string $type = null): array;

    /**
     * @return array{name: string, mime: string, bytes: string}
     */
    public function preview(string $airId, string $airItemId, string $unitId, string $fileId): array;

    /**
     * @return array{air: array<string, mixed>, air_item: array<string, mixed>, unit: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    public function delete(string $actorUserId, string $airId, string $airItemId, string $unitId, string $fileId): array;

    /**
     * @return array{air: array<string, mixed>, air_item: array<string, mixed>, unit: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    public function setPrimary(string $actorUserId, string $airId, string $airItemId, string $unitId, string $fileId): array;
}
