<?php

namespace App\Modules\GSO\Repositories\Contracts;

use App\Modules\GSO\Models\AccountableOfficer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AccountableOfficerRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): AccountableOfficer;

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator;

    public function findByNormalizedName(string $normalizedName, ?string $excludeId = null, bool $withTrashed = false): ?AccountableOfficer;

    public function suggest(string $query, int $limit = 12): Collection;

    public function create(array $data): AccountableOfficer;

    public function save(AccountableOfficer $accountableOfficer): AccountableOfficer;

    public function delete(AccountableOfficer $accountableOfficer): void;

    public function restore(AccountableOfficer $accountableOfficer): void;
}
