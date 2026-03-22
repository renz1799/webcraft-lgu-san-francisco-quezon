<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\AirItemUnit;
use Illuminate\Support\Collection;

interface AirItemUnitRepositoryInterface
{
    /**
     * @return Collection<int, AirItemUnit>
     */
    public function listForAirItem(string $airItemId, bool $withTrashed = false): Collection;

    public function findForAirItemOrFail(string $airItemId, string $unitId, bool $withTrashed = false): AirItemUnit;

    /**
     * @param  array<int, string>  $airItemIds
     * @return array<string, int>
     */
    public function countByAirItemIds(array $airItemIds): array;

    public function create(array $data): AirItemUnit;

    public function save(AirItemUnit $unit): AirItemUnit;

    public function delete(AirItemUnit $unit): void;
}
