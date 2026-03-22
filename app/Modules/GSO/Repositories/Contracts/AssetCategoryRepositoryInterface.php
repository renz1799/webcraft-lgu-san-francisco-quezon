<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\AssetCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AssetCategoryRepositoryInterface
{
    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator;

    public function create(array $data): AssetCategory;

    public function findOrFail(string $id, bool $withTrashed = false): AssetCategory;

    public function save(AssetCategory $assetCategory): AssetCategory;

    public function delete(AssetCategory $assetCategory): void;

    public function restore(AssetCategory $assetCategory): void;
}
