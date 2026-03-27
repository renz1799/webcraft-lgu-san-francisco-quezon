<?php

namespace App\Modules\GSO\Services\Contracts\PTR;

use App\Modules\GSO\Models\Ptr;

interface PtrServiceInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array;

    public function getEditData(string $ptrId): array;

    public function createDraft(string $actorUserId): Ptr;

    public function update(string $actorUserId, string $ptrId, array $payload): Ptr;

    public function delete(string $actorUserId, string $ptrId): Ptr;

    public function restore(string $actorUserId, string $ptrId): Ptr;
}
