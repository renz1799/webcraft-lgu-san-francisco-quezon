<?php

// app/Repositories/Contracts/AuditLogRepositoryInterface.php
namespace App\Core\Repositories\Contracts;

use App\Core\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog;

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

    public function paginate(
        int $perPage = 50,
        array $filters = []
    ): LengthAwarePaginator;

    public function paginateForTable(array $filters, int $page, int $size): LengthAwarePaginator;

    /**
 * @param array<string, mixed> $filters
 * @return \Illuminate\Support\Collection<int, \App\Core\Models\AuditLog>
 */
    public function findForPrint(array $filters);
}
