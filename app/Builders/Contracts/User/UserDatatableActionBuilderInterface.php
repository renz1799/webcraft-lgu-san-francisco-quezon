<?php

namespace App\Builders\Contracts\User;

use App\Models\User;

interface UserDatatableActionBuilderInterface
{
    public function build(User $user): array;
}