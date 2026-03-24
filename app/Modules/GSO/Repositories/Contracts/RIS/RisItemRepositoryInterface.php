<?php

namespace App\Modules\GSO\Repositories\Contracts\RIS;

use App\Modules\GSO\Models\RisItem;
use Illuminate\Support\Collection;

interface RisItemRepositoryInterface
{
    public function listByRisId(string $risId): Collection;

    public function findById(string $id): ?RisItem;

    public function findByRisIdAndItemId(string $risId, string $itemId): ?RisItem;

    public function create(array $data): RisItem;

    public function update(RisItem $risItem, array $data): RisItem;

    public function forceDelete(RisItem $risItem): void;

    public function bulkUpdate(string $risId, array $rowsById): int;
}