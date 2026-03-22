<?php

namespace App\Modules\GSO\Services\Contracts;

interface AirInspectionUnitServiceInterface
{
    /**
     * @return array{air: array<string, mixed>, air_item: array<string, mixed>, units: array<int, array<string, mixed>>}
     */
    public function listForAirItem(string $airId, string $airItemId): array;

    /**
     * @param  array<int, array<string, mixed>>  $units
     * @return array{air: array<string, mixed>, air_item: array<string, mixed>, units: array<int, array<string, mixed>>}
     */
    public function saveForAirItem(string $actorUserId, string $airId, string $airItemId, array $units): array;

    /**
     * @return array{air: array<string, mixed>, air_item: array<string, mixed>, units: array<int, array<string, mixed>>}
     */
    public function deleteUnit(string $actorUserId, string $airId, string $airItemId, string $unitId): array;
}
