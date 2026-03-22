<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\FundSource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FundSourceRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): FundSource;

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator;

    public function create(array $data): FundSource;

    public function save(FundSource $fundSource): FundSource;

    public function delete(FundSource $fundSource): void;

    public function restore(FundSource $fundSource): void;
}
