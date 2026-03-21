<?php

namespace App\Core\Builders\Contracts\User;

use App\Core\Models\User;

interface UserTaskReassignOptionBuilderInterface
{
    public function build(User $user): array;
}