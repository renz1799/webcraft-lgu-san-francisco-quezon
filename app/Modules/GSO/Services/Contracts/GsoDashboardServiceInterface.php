<?php

namespace App\Modules\GSO\Services\Contracts;

use App\Core\Models\User;

interface GsoDashboardServiceInterface
{
    public function build(User $user): array;
}
