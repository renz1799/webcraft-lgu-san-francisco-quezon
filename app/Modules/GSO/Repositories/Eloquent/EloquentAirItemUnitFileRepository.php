<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\AirItemUnitFile;
use App\Modules\GSO\Repositories\Contracts\AirItemUnitFileRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentAirItemUnitFileRepository implements AirItemUnitFileRepositoryInterface
{
    public function listForUnit(string $unitId, bool $withTrashed = false): Collection
    {
        $query = AirItemUnitFile::query()
            ->where('air_item_unit_id', $unitId)
            ->orderByDesc('is_primary')
            ->orderBy('position')
            ->orderByDesc('created_at');

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->get();
    }

    public function findForUnitOrFail(string $unitId, string $fileId, bool $withTrashed = false): AirItemUnitFile
    {
        $query = AirItemUnitFile::query()
            ->where('air_item_unit_id', $unitId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($fileId);
    }

    public function create(array $data): AirItemUnitFile
    {
        return AirItemUnitFile::query()->create($data);
    }

    public function save(AirItemUnitFile $file): AirItemUnitFile
    {
        $file->save();

        return $file->refresh();
    }

    public function nextPositionForUnit(string $unitId): int
    {
        return (int) AirItemUnitFile::query()
            ->where('air_item_unit_id', $unitId)
            ->max('position') + 1;
    }

    public function clearPrimaryForUnit(string $unitId, ?string $exceptFileId = null): void
    {
        AirItemUnitFile::query()
            ->where('air_item_unit_id', $unitId)
            ->when($exceptFileId !== null && trim($exceptFileId) !== '', fn ($query) => $query->where('id', '!=', $exceptFileId))
            ->where('is_primary', true)
            ->update(['is_primary' => false]);
    }

    public function hasActiveFiles(string $unitId): bool
    {
        return AirItemUnitFile::query()
            ->where('air_item_unit_id', $unitId)
            ->whereNull('deleted_at')
            ->exists();
    }

    public function delete(AirItemUnitFile $file): void
    {
        $file->delete();
    }
}
