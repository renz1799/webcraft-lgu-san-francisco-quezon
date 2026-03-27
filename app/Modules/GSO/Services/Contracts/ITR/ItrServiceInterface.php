<?php

namespace App\Modules\GSO\Services\Contracts\ITR;

use App\Modules\GSO\Models\Itr;

interface ItrServiceInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array;

    public function getEditData(string $itrId): array;

    public function createDraft(string $actorUserId): Itr;

    public function update(string $actorUserId, string $itrId, array $payload): Itr;

    public function delete(string $actorUserId, string $itrId): Itr;

    public function restore(string $actorUserId, string $itrId): Itr;
}



