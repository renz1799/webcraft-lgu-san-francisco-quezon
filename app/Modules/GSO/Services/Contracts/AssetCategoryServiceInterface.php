<?php

namespace App\Modules\GSO\Services\Contracts;

use App\Modules\GSO\Models\AssetCategory;

interface AssetCategoryServiceInterface
{
    public function datatable(array $params): array;

    public function create(string $actorUserId, array $data): AssetCategory;

    public function update(string $actorUserId, string $assetCategoryId, array $data): AssetCategory;

    public function delete(string $actorUserId, string $assetCategoryId): void;

    public function restore(string $actorUserId, string $assetCategoryId): void;
}
