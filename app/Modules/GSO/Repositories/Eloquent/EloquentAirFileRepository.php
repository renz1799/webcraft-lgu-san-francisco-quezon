<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\AirFile;
use App\Modules\GSO\Repositories\Contracts\AirFileRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentAirFileRepository implements AirFileRepositoryInterface
{
    public function listForAir(string $airId, bool $withTrashed = false): Collection
    {
        $query = AirFile::query()
            ->where('air_id', $airId)
            ->orderByDesc('is_primary')
            ->orderBy('position')
            ->orderByDesc('created_at');

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->get();
    }

    public function findForAirOrFail(string $airId, string $fileId, bool $withTrashed = false): AirFile
    {
        $query = AirFile::query()
            ->where('air_id', $airId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($fileId);
    }

    public function create(array $data): AirFile
    {
        return AirFile::query()->create($data);
    }

    public function save(AirFile $file): AirFile
    {
        $file->save();

        return $file->refresh();
    }

    public function nextPositionForAir(string $airId): int
    {
        return (int) AirFile::query()
            ->where('air_id', $airId)
            ->max('position') + 1;
    }

    public function clearPrimaryForAir(string $airId, ?string $exceptFileId = null): void
    {
        AirFile::query()
            ->where('air_id', $airId)
            ->when($exceptFileId !== null && trim($exceptFileId) !== '', fn ($query) => $query->where('id', '!=', $exceptFileId))
            ->where('is_primary', true)
            ->update(['is_primary' => false]);
    }

    public function delete(AirFile $file): void
    {
        $file->delete();
    }
}
