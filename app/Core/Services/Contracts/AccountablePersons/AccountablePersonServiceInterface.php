<?php

namespace App\Core\Services\Contracts\AccountablePersons;

use App\Core\Models\AccountablePerson;

interface AccountablePersonServiceInterface
{
    public function datatable(array $params): array;

    public function suggest(string $query): array;

    /**
     * @return array{
     *     officer: array<string, mixed>,
     *     created: bool,
     *     restored: bool,
     *     reused: bool
     * }
     */
    public function createOrResolve(string $actorUserId, array $data): array;

    public function create(string $actorUserId, array $data): AccountablePerson;

    public function update(string $actorUserId, string $accountablePersonId, array $data): AccountablePerson;

    public function delete(string $actorUserId, string $accountablePersonId): void;

    public function restore(string $actorUserId, string $accountablePersonId): void;
}
