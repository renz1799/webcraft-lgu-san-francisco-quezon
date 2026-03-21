<?php

namespace App\Core\Builders\User;

use App\Core\Builders\Contracts\User\UserTaskReassignOptionBuilderInterface;
use App\Core\Models\User;

class UserTaskReassignOptionBuilder implements UserTaskReassignOptionBuilderInterface
{
    public function build(User $user): array
    {
        $name = $user->profile?->full_name ?: ($user->username ?: 'Unknown User');

        return [
            'id' => (string) $user->id,
            'name' => trim((string) $name),
        ];
    }
}