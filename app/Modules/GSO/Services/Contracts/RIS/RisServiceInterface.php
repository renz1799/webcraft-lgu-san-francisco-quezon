<?php

namespace App\Modules\GSO\Services\Contracts\RIS;

use App\Modules\GSO\Models\Ris;

interface RisServiceInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array;

    public function generateFromAir(string $actorUserId, string $airId, array $overrides = []): Ris;

    public function getEditData(string $risId): array;

    public function updateRis(string $actorUserId, string $risId, array $data): Ris;

    public function deleteRis(string $actorUserId, string $risId): void;

    public function restoreRis(string $actorUserId, string $risId): void;

    public function createDraft(string $actorUserId): Ris;
}
