<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\Inspection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InspectionRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): Inspection;

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator;

    public function create(array $data): Inspection;

    public function save(Inspection $inspection): Inspection;

    public function delete(Inspection $inspection): void;

    public function restore(Inspection $inspection): void;
}
