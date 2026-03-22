<?php

namespace App\Modules\GSO\Services\Contracts;

use App\Modules\GSO\Models\Inspection;

interface InspectionServiceInterface
{
    public function datatable(array $params): array;

    public function getForEdit(string $inspectionId): array;

    public function create(string $actorUserId, array $data): Inspection;

    public function update(string $actorUserId, string $inspectionId, array $data): Inspection;

    public function delete(string $actorUserId, string $inspectionId): void;

    public function restore(string $actorUserId, string $inspectionId): void;
}
