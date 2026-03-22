<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\AirItem;
use Illuminate\Support\Collection;

interface AirItemRepositoryInterface
{
    public function findOrFail(string $id): AirItem;

    /**
     * @return Collection<int, AirItem>
     */
    public function listByAirId(string $airId): Collection;

    public function countByAirId(string $airId): int;

    public function create(array $data): AirItem;

    public function save(AirItem $airItem): AirItem;

    public function delete(AirItem $airItem): void;
}
