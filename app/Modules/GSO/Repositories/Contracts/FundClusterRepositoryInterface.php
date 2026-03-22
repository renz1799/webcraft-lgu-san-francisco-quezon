<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\FundCluster;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface FundClusterRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): FundCluster;

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator;

    public function activeOptions(): Collection;

    public function create(array $data): FundCluster;

    public function save(FundCluster $fundCluster): FundCluster;

    public function delete(FundCluster $fundCluster): void;

    public function restore(FundCluster $fundCluster): void;
}
