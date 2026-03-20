<?php

namespace App\Builders\Contracts\Tasks;

use App\Models\User;

interface TaskReassignmentNoteBuilderInterface
{
    public function build(?User $fromUser, ?User $toUser, ?string $note = null): array;
}
