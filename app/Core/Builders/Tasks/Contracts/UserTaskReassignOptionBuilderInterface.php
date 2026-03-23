<?php

namespace App\Core\Builders\Tasks\Contracts;

use App\Core\Models\User;

interface UserTaskReassignOptionBuilderInterface
{
    public function build(User $user): array;
}
