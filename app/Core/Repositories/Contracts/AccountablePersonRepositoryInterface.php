<?php

namespace App\Core\Repositories\Contracts;

use App\Core\Models\AccountablePerson;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AccountablePersonRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): AccountablePerson;

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator;

    public function findByNormalizedName(string $normalizedName, ?string $excludeId = null, bool $withTrashed = false): ?AccountablePerson;

    public function suggest(string $query, int $limit = 12): Collection;

    public function create(array $data): AccountablePerson;

    public function save(AccountablePerson $accountablePerson): AccountablePerson;

    public function delete(AccountablePerson $accountablePerson): void;

    public function restore(AccountablePerson $accountablePerson): void;
}
