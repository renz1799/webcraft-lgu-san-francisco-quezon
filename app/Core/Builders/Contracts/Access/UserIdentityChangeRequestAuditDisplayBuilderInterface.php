<?php

namespace App\Core\Builders\Contracts\Access;

use App\Core\Models\User;
use App\Core\Models\UserIdentityChangeRequest;

interface UserIdentityChangeRequestAuditDisplayBuilderInterface
{
    public function buildRequestedDisplay(User $user, UserIdentityChangeRequest $request): array;

    public function buildApprovedDisplay(User $user, UserIdentityChangeRequest $request, User $reviewer): array;

    public function buildRejectedDisplay(User $user, UserIdentityChangeRequest $request, User $reviewer): array;
}
