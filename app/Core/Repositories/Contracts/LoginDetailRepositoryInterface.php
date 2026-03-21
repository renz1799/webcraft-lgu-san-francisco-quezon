<?php

namespace App\Core\Repositories\Contracts;

use App\Core\Models\LoginDetail;
use Illuminate\Support\Collection;

interface LoginDetailRepositoryInterface
{
    public function create(array $data): LoginDetail;

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
    public function datatable(string $moduleId, array $filters, int $page = 1, int $size = 15): array;

    /**
     * @return Collection<int, LoginDetail>
     */
    public function recentForUser(string $moduleId, string $userId, int $limit = 4): Collection;
}
