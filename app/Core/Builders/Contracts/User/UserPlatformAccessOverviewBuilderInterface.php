<?php

namespace App\Core\Builders\Contracts\User;

use App\Core\Models\User;

interface UserPlatformAccessOverviewBuilderInterface
{
    public function build(User $user): array;
}
