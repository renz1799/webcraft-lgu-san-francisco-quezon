<?php

namespace App\Core\Builders\Tasks\Contracts;

use App\Core\Models\User;

interface TaskReassignmentNoteBuilderInterface
{
    public function build(?User $fromUser, ?User $toUser, ?string $note = null): array;
}
