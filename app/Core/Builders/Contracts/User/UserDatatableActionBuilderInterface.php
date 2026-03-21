<?php

namespace App\Core\Builders\Contracts\User;

use App\Core\Models\User;

interface UserDatatableActionBuilderInterface
{
    public function build(User $user): array;
}