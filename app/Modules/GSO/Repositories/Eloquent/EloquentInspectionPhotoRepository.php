<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\InspectionPhoto;
use App\Modules\GSO\Repositories\Contracts\InspectionPhotoRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentInspectionPhotoRepository implements InspectionPhotoRepositoryInterface
{
    public function listForInspection(string $inspectionId, bool $withTrashed = false): Collection
    {
        $query = InspectionPhoto::query()
            ->where('inspection_id', $inspectionId)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->get();
    }

    public function findForInspectionOrFail(string $inspectionId, string $photoId, bool $withTrashed = false): InspectionPhoto
    {
        $query = InspectionPhoto::query()
            ->where('inspection_id', $inspectionId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($photoId);
    }

    public function create(array $data): InspectionPhoto
    {
        return InspectionPhoto::query()->create($data);
    }

    public function delete(InspectionPhoto $photo): void
    {
        $photo->delete();
    }
}
