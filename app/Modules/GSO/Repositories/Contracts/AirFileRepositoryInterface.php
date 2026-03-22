<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\AirFile;
use Illuminate\Support\Collection;

interface AirFileRepositoryInterface
{
    /**
     * @return Collection<int, AirFile>
     */
    public function listForAir(string $airId, bool $withTrashed = false): Collection;

    public function findForAirOrFail(string $airId, string $fileId, bool $withTrashed = false): AirFile;

    public function create(array $data): AirFile;

    public function save(AirFile $file): AirFile;

    public function nextPositionForAir(string $airId): int;

    public function clearPrimaryForAir(string $airId, ?string $exceptFileId = null): void;

    public function delete(AirFile $file): void;
}
