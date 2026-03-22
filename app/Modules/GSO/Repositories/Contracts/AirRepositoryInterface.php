<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\Air;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AirRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): Air;

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator;

    public function create(array $data): Air;

    public function save(Air $air): Air;

    public function delete(Air $air): void;

    public function restore(Air $air): void;

    public function forceDelete(Air $air): void;
}
