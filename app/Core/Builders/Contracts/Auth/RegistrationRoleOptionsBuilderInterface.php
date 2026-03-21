<?php

namespace App\Core\Builders\Contracts\Auth;

use Illuminate\Support\Collection;

interface RegistrationRoleOptionsBuilderInterface
{
    public function build(Collection $roles): array;
}