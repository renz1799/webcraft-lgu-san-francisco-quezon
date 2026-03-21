<?php

namespace App\Core\Services\Contracts\Auth;

use App\Core\Models\User;

interface AuthServiceInterface
{
    public function attemptLogin(array $data): bool;
    public function logout(): void;
}
