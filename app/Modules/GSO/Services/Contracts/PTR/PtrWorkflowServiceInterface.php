<?php

namespace App\Modules\GSO\Services\Contracts\PTR;

use App\Modules\GSO\Models\Ptr;

interface PtrWorkflowServiceInterface
{
    public function submit(string $actorUserId, string $ptrId): Ptr;

    public function reopen(string $actorUserId, string $ptrId): Ptr;

    public function finalize(string $actorUserId, string $ptrId): Ptr;

    public function cancel(string $actorUserId, string $ptrId, ?string $reason = null): Ptr;
}
