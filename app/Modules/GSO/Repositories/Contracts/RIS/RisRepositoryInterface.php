<?php

namespace App\Modules\GSO\Repositories\Contracts\RIS;

use App\Modules\GSO\Models\Ris;

interface RisRepositoryInterface
{
    /**
     * Manual pagination payload for Tabulator.
     *
     * Return shape:
     * [
     *   'data' => array<array>,
     *   'last_page' => int,
     *   'total' => int,
     *   'recordsTotal' => int,
     *   'recordsFiltered' => int,
     * ]
     */
    public function datatable(array $filters, int $page = 1, int $size = 15): array;
    
    public function findById(string $id): ?Ris;

    public function findByAirId(string $airId): ?Ris;

    public function create(array $data): Ris;

    public function update(Ris $ris, array $data): Ris;

    public function delete(Ris $ris): void;

    public function restore(Ris $ris): void;
}
