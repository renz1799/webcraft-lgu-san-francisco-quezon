<?php

namespace App\Builders\Tasks;

use App\Builders\Contracts\Tasks\TaskReassignmentNoteBuilderInterface;
use App\Builders\Contracts\User\UserTaskReassignOptionBuilderInterface;
use App\Models\User;

class TaskReassignmentNoteBuilder implements TaskReassignmentNoteBuilderInterface
{
    public function __construct(
        private readonly UserTaskReassignOptionBuilderInterface $userOptionBuilder,
    ) {}

    public function build(?User $fromUser, ?User $toUser, ?string $note = null): array
    {
        $from = $fromUser
            ? $this->userOptionBuilder->build($fromUser)
            : ['id' => null, 'name' => 'Unassigned'];

        $to = $toUser
            ? $this->userOptionBuilder->build($toUser)
            : ['id' => null, 'name' => 'Unassigned'];

        $customNote = trim((string) $note);

        $lines = ["Task reassigned: {$from['name']} -> {$to['name']}."];
        if ($customNote !== '') {
            $lines[] = "Note: {$customNote}";
        }

        return [
            'note' => implode("\n", $lines),
            'meta' => [
                'from_user_id' => $from['id'],
                'to_user_id' => $to['id'],
                'from_user_name' => $from['name'],
                'to_user_name' => $to['name'],
                'custom_note' => $customNote !== '' ? $customNote : null,
            ],
        ];
    }
}
