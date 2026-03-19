<?php

namespace App\Services\Contracts\Auth;

use App\Data\Auth\RegisterUserData;
use App\Models\User;

interface RegisterUserServiceInterface
{
    public function register(User $actor, RegisterUserData $data): User;
}