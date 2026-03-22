<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Core\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DepartmentRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): Department;

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator;

    public function activeOptions(): Collection;

    public function create(array $data): Department;

    public function save(Department $department): Department;

    public function delete(Department $department): void;

    public function restore(Department $department): void;
}
