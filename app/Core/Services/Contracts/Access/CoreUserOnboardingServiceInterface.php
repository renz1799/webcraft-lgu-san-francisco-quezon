<?php

namespace App\Core\Services\Contracts\Access;

use App\Core\Data\Users\CoreUserOnboardingData;
use App\Core\Models\User;

interface CoreUserOnboardingServiceInterface
{
    public function getCreateData(): array;

    public function onboard(User $actor, CoreUserOnboardingData $data): array;
}
