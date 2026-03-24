<?php

namespace App\Modules\GSO\Data\Contracts\RIS;

interface RisItemDataProviderInterface
{
    public function getFundSourceContext(string $fundSourceId): ?array;

    /**
     * @param  array<int, string>  $excludeItemIds
     * @return array<int, array<string, mixed>>
     */
    public function getConsumableSuggestionRows(
        string $risFundSourceId,
        string $search = '',
        array $excludeItemIds = [],
    ): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEditRows(string $risId, string $fundSourceId): array;

    public function getOnHandForItemAndFundSource(string $itemId, string $fundSourceId): int;

    public function getItemSnapshot(string $itemId): ?array;
}