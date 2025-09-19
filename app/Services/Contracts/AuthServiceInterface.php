<?php

namespace App\Services\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    public function register(array $data): User;
    public function attemptLogin(array $data): bool;
    public function logout(): void;
}
