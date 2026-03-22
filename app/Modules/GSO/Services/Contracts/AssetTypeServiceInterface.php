<?php

namespace App\Modules\GSO\Services\Contracts;

use App\Modules\GSO\Models\AssetType;
use Illuminate\Support\Collection;

interface AssetTypeServiceInterface
{
    public function datatable(array $params): array;

    public function optionsForSelect(): Collection;

    public function create(string $actorUserId, array $data): AssetType;

    public function update(string $actorUserId, string $assetTypeId, array $data): AssetType;

    public function delete(string $actorUserId, string $assetTypeId): void;

    public function restore(string $actorUserId, string $assetTypeId): void;
}
