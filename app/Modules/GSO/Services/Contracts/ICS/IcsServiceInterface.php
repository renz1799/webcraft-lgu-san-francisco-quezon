<?php

namespace App\Modules\GSO\Services\Contracts\ICS;

use App\Modules\GSO\Models\Ics;

interface IcsServiceInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array;

    public function getEditData(string $icsId): array;

    public function createDraft(string $actorUserId): Ics;

    public function update(string $actorUserId, string $icsId, array $payload): Ics;

    public function delete(string $actorUserId, string $icsId): Ics;

    public function restore(string $actorUserId, string $icsId): Ics;
}
