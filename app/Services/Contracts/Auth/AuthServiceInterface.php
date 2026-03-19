<?php

namespace App\Services\Contracts\Auth;

use App\Models\User;

interface AuthServiceInterface
{
    public function attemptLogin(array $data): bool;
    public function logout(): void;
}
