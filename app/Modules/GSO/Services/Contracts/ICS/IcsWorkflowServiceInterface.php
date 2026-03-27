<?php

namespace App\Modules\GSO\Services\Contracts\ICS;

use App\Modules\GSO\Models\Ics;

interface IcsWorkflowServiceInterface
{
    public function submit(string $actorUserId, string $icsId): Ics;

    public function reopen(string $actorUserId, string $icsId): Ics;

    public function finalize(string $actorUserId, string $icsId): Ics;

    public function cancel(string $actorUserId, string $icsId, ?string $reason = null): Ics;
}
