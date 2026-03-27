<?php

namespace App\Modules\GSO\Repositories\Contracts\PAR;

use App\Modules\GSO\Models\Par;

interface ParRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array;
    
    public function create(array $data): Par;

    public function findOrFail(string $id): Par;

    public function update(Par $par, array $data): Par;

    public function findWithTrashedOrFail(string $id): Par;

    public function softDelete(Par $par): void;

    public function restore(Par $par): void;
}
