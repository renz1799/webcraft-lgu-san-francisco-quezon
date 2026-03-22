<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\InspectionPhoto;
use Illuminate\Support\Collection;

interface InspectionPhotoRepositoryInterface
{
    /**
     * @return Collection<int, InspectionPhoto>
     */
    public function listForInspection(string $inspectionId, bool $withTrashed = false): Collection;

    public function findForInspectionOrFail(string $inspectionId, string $photoId, bool $withTrashed = false): InspectionPhoto;

    public function create(array $data): InspectionPhoto;

    public function delete(InspectionPhoto $photo): void;
}
