<?php

namespace App\Modules\GSO\Services\Contracts\PAR;

use App\Modules\GSO\Models\Par;

interface ParWorkflowServiceInterface
{
    public function submit(string $actorUserId, string $parId): Par;

    public function reopen(string $actorUserId, string $parId): Par;

    public function finalize(string $actorUserId, string $parId): Par;

    public function cancel(string $actorUserId, string $parId, ?string $reason = null): Par;
}
