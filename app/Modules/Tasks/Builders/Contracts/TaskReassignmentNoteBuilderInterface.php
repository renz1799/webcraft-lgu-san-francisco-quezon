<?php

namespace App\Modules\Tasks\Builders\Contracts;

use App\Core\Models\User;

interface TaskReassignmentNoteBuilderInterface
{
    public function build(?User $fromUser, ?User $toUser, ?string $note = null): array;
}
