<?php

namespace App\Modules\GSO\Services\Contracts;

interface AirFileServiceInterface
{
    /**
     * @return array{air: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    public function listForAir(string $airId): array;

    /**
     * @param  array<int, mixed>  $files
     * @return array{air: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    public function upload(string $actorUserId, string $airId, array $files, ?string $type = null): array;

    /**
     * @return array{name: string, mime: string, bytes: string}
     */
    public function preview(string $airId, string $fileId): array;

    /**
     * @return array{air: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    public function delete(string $actorUserId, string $airId, string $fileId): array;

    /**
     * @return array{air: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    public function setPrimary(string $actorUserId, string $airId, string $fileId): array;
}
