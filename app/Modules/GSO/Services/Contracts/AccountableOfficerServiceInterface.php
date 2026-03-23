<?php

namespace App\Modules\GSO\Services\Contracts;

use App\Modules\GSO\Models\AccountableOfficer;

interface AccountableOfficerServiceInterface
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

    public function create(string $actorUserId, array $data): AccountableOfficer;

    public function update(string $actorUserId, string $accountableOfficerId, array $data): AccountableOfficer;

    public function delete(string $actorUserId, string $accountableOfficerId): void;

    public function restore(string $actorUserId, string $accountableOfficerId): void;
}
