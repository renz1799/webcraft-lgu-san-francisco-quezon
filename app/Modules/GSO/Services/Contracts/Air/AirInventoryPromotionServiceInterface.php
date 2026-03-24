<?php

namespace App\Modules\GSO\Services\Contracts\Air;

interface AirInventoryPromotionServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getEligibility(string $airId): array;

    /**
     * @param  array<int, string>  $airItemUnitIds
     * @return array<string, mixed>
     */
    public function promote(
        string $actorUserId,
        string $airId,
        array $airItemUnitIds = [],
        ?string $actorName = null,
    ): array;
}
