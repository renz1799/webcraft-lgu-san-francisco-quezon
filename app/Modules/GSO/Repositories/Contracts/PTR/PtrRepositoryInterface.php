<?php

namespace App\Modules\GSO\Repositories\Contracts\PTR;

use App\Modules\GSO\Models\Ptr;

interface PtrRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array;

    public function create(array $data): Ptr;

    public function findOrFail(string $id): Ptr;

    public function update(Ptr $ptr, array $data): Ptr;

    public function findWithTrashedOrFail(string $id): Ptr;

    public function softDelete(Ptr $ptr): void;

    public function restore(Ptr $ptr): void;
}
