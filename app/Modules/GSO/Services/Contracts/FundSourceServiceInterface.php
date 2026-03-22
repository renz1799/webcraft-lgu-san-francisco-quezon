<?php

namespace App\Modules\GSO\Services\Contracts;

use App\Modules\GSO\Models\FundSource;

interface FundSourceServiceInterface
{
    public function datatable(array $params): array;

    public function create(string $actorUserId, array $data): FundSource;

    public function update(string $actorUserId, string $fundSourceId, array $data): FundSource;

    public function delete(string $actorUserId, string $fundSourceId): void;

    public function restore(string $actorUserId, string $fundSourceId): void;
}
