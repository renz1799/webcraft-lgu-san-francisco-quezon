<?php

namespace App\Modules\GSO\Services\Contracts;

interface InspectionPhotoServiceInterface
{
    /**
     * @return array{inspection: array<string, mixed>, photos: array<int, array<string, mixed>>}
     */
    public function listForInspection(string $inspectionId): array;

    /**
     * @param  array<int, mixed>  $files
     * @return array{inspection: array<string, mixed>, photos: array<int, array<string, mixed>>}
     */
    public function upload(string $actorUserId, string $inspectionId, array $files): array;

    /**
     * @return array{inspection: array<string, mixed>, photos: array<int, array<string, mixed>>}
     */
    public function delete(string $actorUserId, string $inspectionId, string $photoId): array;
}
