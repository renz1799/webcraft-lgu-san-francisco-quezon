<?php

namespace App\Modules\GSO\Services\Contracts;

use App\Modules\GSO\Models\FundCluster;
use Illuminate\Support\Collection;

interface FundClusterServiceInterface
{
    public function datatable(array $params): array;

    public function optionsForSelect(): Collection;

    public function create(string $actorUserId, array $data): FundCluster;

    public function update(string $actorUserId, string $fundClusterId, array $data): FundCluster;

    public function delete(string $actorUserId, string $fundClusterId): void;

    public function restore(string $actorUserId, string $fundClusterId): void;
}
