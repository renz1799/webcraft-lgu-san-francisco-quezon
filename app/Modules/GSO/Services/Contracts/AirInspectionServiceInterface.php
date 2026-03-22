<?php

namespace App\Modules\GSO\Services\Contracts;

interface AirInspectionServiceInterface
{
    /**
     * @return array{air: array<string, mixed>, items: array<int, array<string, mixed>>}
     */
    public function getForInspection(string $airId): array;

    /**
     * @param  array<string, mixed>  $data
     * @return array{air: array<string, mixed>, items: array<int, array<string, mixed>>}
     */
    public function saveInspection(string $actorUserId, string $airId, array $data): array;

    /**
     * @return array{air: array<string, mixed>, items: array<int, array<string, mixed>>}
     */
    public function finalizeInspection(string $actorUserId, string $airId): array;
}
