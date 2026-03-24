<?php

namespace App\Modules\GSO\Services\Contracts\RIS;

use App\Modules\GSO\Models\Ris;

interface RisWorkflowServiceInterface
{
    public function submit(string $actorUserId, string $risId): Ris;

    public function approveIssue(string $actorUserId, string $risId): Ris;

    public function reject(string $actorUserId, string $risId, ?string $reason = null): Ris;

    public function reopen(string $actorUserId, string $risId): Ris;

    public function revertToDraft(string $actorUserId, string $risId): Ris;
}
