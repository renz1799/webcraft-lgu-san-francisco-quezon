<?php

namespace App\Modules\GSO\Repositories\Contracts\ICS;

use App\Modules\GSO\Models\Ics;

interface IcsRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array;

    public function create(array $data): Ics;

    public function findOrFail(string $id): Ics;

    public function update(Ics $ics, array $data): Ics;

    public function findWithTrashedOrFail(string $id): Ics;

    public function softDelete(Ics $ics): void;

    public function restore(Ics $ics): void;
}
