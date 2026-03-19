<?php

namespace App\Builders\Contracts\User;

use App\Models\User;

interface UserDatatableRowBuilderInterface
{
    public function build(User $user): array;
}