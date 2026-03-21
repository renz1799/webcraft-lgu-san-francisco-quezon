<?php

namespace App\Core\Services\Contracts\Auth;

use App\Core\Data\Auth\RegisterUserData;
use App\Core\Models\User;

interface RegisterUserServiceInterface
{
    public function register(User $actor, RegisterUserData $data): User;
}