<?php

namespace App\Core\Services\Contracts\Access;

use App\Core\Models\User;
use App\Core\Models\UserIdentityChangeRequest;

interface UserIdentityChangeRequestServiceInterface
{
    public function latestForUser(User $user): ?UserIdentityChangeRequest;

    public function submitFromProfileUpdate(User $user, array $identityData, ?string $reason = null): array;

    public function indexData(User $reviewer, array $filters = [], int $perPage = 15): array;

    public function showData(User $reviewer, string $requestId): array;

    public function approve(string $requestId, User $reviewer, ?string $reviewNotes = null): UserIdentityChangeRequest;

    public function reject(string $requestId, User $reviewer, ?string $reviewNotes = null): UserIdentityChangeRequest;
}
