<?php

namespace App\Modules\GSO\Repositories\Contracts\WMR;

use App\Modules\GSO\Models\Wmr;

interface WmrRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array;

    public function create(array $data): Wmr;

    public function findOrFail(string $id): Wmr;

    public function update(Wmr $wmr, array $data): Wmr;

    public function findWithTrashedOrFail(string $id): Wmr;

    public function softDelete(Wmr $wmr): void;

    public function restore(Wmr $wmr): void;
}
