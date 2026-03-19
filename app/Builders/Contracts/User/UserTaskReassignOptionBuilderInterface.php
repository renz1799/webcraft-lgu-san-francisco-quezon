<?php

namespace App\Builders\Contracts\User;

use App\Models\User;

interface UserTaskReassignOptionBuilderInterface
{
    public function build(User $user): array;
}