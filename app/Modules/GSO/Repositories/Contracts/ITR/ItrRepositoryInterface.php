<?php

namespace App\Modules\GSO\Repositories\Contracts\ITR;

use App\Modules\GSO\Models\Itr;

interface ItrRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array;

    public function create(array $data): Itr;

    public function findOrFail(string $id): Itr;

    public function update(Itr $itr, array $data): Itr;

    public function findWithTrashedOrFail(string $id): Itr;

    public function softDelete(Itr $itr): void;

    public function restore(Itr $itr): void;
}


