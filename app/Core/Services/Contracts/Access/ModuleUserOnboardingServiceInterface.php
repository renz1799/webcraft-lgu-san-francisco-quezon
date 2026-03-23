<?php

namespace App\Core\Services\Contracts\Access;

use App\Core\Data\Users\ModuleUserOnboardingData;

interface ModuleUserOnboardingServiceInterface
{
    public function getCreateData(): array;

    public function onboard(ModuleUserOnboardingData $data): array;
}
