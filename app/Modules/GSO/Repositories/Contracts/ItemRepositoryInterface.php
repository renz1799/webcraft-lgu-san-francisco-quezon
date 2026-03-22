<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ItemRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): Item;

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator;

    public function create(array $data): Item;

    public function save(Item $item): Item;

    public function delete(Item $item): void;

    public function restore(Item $item): void;
}
