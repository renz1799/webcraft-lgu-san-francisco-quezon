<?php

namespace App\Modules\GSO\Services\Contracts\ITR;

use App\Modules\GSO\Models\Itr;

interface ItrWorkflowServiceInterface
{
    public function submit(string $actorUserId, string $itrId): Itr;

    public function reopen(string $actorUserId, string $itrId): Itr;

    public function finalize(string $actorUserId, string $itrId): Itr;

    public function cancel(string $actorUserId, string $itrId, ?string $reason = null): Itr;
}




