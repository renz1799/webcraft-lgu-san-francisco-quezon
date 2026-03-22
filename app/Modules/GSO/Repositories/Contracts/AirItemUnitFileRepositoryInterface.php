<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\AirItemUnitFile;
use Illuminate\Support\Collection;

interface AirItemUnitFileRepositoryInterface
{
    /**
     * @return Collection<int, AirItemUnitFile>
     */
    public function listForUnit(string $unitId, bool $withTrashed = false): Collection;

    public function findForUnitOrFail(string $unitId, string $fileId, bool $withTrashed = false): AirItemUnitFile;

    public function create(array $data): AirItemUnitFile;

    public function save(AirItemUnitFile $file): AirItemUnitFile;

    public function nextPositionForUnit(string $unitId): int;

    public function clearPrimaryForUnit(string $unitId, ?string $exceptFileId = null): void;

    public function hasActiveFiles(string $unitId): bool;

    public function delete(AirItemUnitFile $file): void;
}
