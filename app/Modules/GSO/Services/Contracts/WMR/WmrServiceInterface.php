<?php

namespace App\Modules\GSO\Services\Contracts\WMR;

use App\Modules\GSO\Models\Wmr;

interface WmrServiceInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array;

    public function getEditData(string $wmrId): array;

    public function createDraft(string $actorUserId): Wmr;

    public function update(string $actorUserId, string $wmrId, array $payload): Wmr;

    public function delete(string $actorUserId, string $wmrId): Wmr;

    public function restore(string $actorUserId, string $wmrId): Wmr;
}

