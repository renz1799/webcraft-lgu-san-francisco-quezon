<?php

namespace App\Modules\GSO\Services\Contracts;

use App\Modules\GSO\Models\Air;

interface AirServiceInterface
{
    /**
     * @param  array<string, mixed>  $params
     * @return array{data: array<int, array<string, mixed>>, last_page: int, total: int}
     */
    public function datatable(array $params): array;

    /**
     * @return array<string, mixed>
     */
    public function getForEdit(string $airId): array;

    public function createBlankDraft(string $actorUserId): Air;

    public function updateDraft(string $actorUserId, string $airId, array $data): Air;

    public function submitDraft(string $actorUserId, string $airId): Air;

    public function delete(string $actorUserId, string $airId): void;

    public function restore(string $actorUserId, string $airId): void;

    public function forceDelete(string $actorUserId, string $airId): void;
}
