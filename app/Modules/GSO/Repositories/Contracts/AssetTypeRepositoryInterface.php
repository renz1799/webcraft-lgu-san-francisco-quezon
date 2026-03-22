<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\AssetType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AssetTypeRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): AssetType;

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator;

    public function activeOptions(): Collection;

    public function create(array $data): AssetType;

    public function save(AssetType $assetType): AssetType;

    public function delete(AssetType $assetType): void;

    public function restore(AssetType $assetType): void;
}
