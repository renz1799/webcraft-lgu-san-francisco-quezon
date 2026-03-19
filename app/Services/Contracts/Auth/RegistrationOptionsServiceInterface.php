<?php

namespace App\Services\Contracts\Auth;

use Illuminate\Support\Collection;

interface RegistrationOptionsServiceInterface
{
    public function roles(): Collection;

    public function roleOptions(): array;
}