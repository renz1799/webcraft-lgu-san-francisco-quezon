<?php

namespace App\Modules\GSO\Services\Contracts\WMR;

use App\Modules\GSO\Models\Wmr;

interface WmrWorkflowServiceInterface
{
    public function submit(string $actorUserId, string $wmrId): Wmr;

    public function approve(string $actorUserId, string $wmrId): Wmr;

    public function reopen(string $actorUserId, string $wmrId): Wmr;

    public function finalize(string $actorUserId, string $wmrId): Wmr;

    public function cancel(string $actorUserId, string $wmrId, ?string $reason = null): Wmr;
}
