<?php

namespace App\Modules\GSO\Services\Contracts;

interface StockServiceInterface
{
    /**
     * @param  array<string, mixed>  $params
     * @return array{data: array<int, array<string, mixed>>, last_page: int, total: int}
     */
    public function datatable(array $params): array;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getLedgerViewData(string $itemId, array $filters): array;

    /**
     * @return array<string, mixed>
     */
    public function getCardPrintViewData(string $itemId, ?string $fundSourceId, ?string $asOf = null): array;

    /**
     * @return array<string, mixed>
     */
    public function adjustManual(
        string $actorUserId,
        string $actorName,
        string $itemId,
        string $type,
        int $qty,
        ?string $fundSourceId = null,
        ?string $remarks = null
    ): array;
}
