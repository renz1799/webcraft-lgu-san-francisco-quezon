<?php

namespace App\Builders\User;

use App\Builders\Contracts\User\UserTaskReassignOptionBuilderInterface;
use App\Models\User;

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