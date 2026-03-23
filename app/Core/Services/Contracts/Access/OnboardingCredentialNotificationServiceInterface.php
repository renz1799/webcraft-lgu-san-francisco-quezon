<?php

namespace App\Core\Services\Contracts\Access;

use App\Core\Models\User;

interface OnboardingCredentialNotificationServiceInterface
{
    public function resolveType(User $user, bool $identityCreated = false): string;

    public function send(
        User $user,
        string $moduleName,
        ?string $departmentName = null,
        ?string $roleName = null,
        bool $identityCreated = false,
        bool $membershipActive = true,
    ): array;
}
