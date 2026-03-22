<?php

namespace App\Modules\GSO\Services;

use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Services\Contracts\RpcspReportServiceInterface;
use Illuminate\Database\Eloquent\Builder;

class RpcspReportService extends AbstractPhysicalCountReportService implements RpcspReportServiceInterface
{
    protected function reportCode(): string
    {
        return 'RPCSP';
    }

    protected function itemFallbackLabel(): string
    {
        return 'Semi-Expendable Property';
    }

    protected function applyClassificationScope(Builder $query): Builder
    {
        return $query->where('is_ics', true);
    }

    protected function matchesInventoryItem(InventoryItem $inventoryItem): bool
    {
        return (bool) ($inventoryItem->is_ics ?? false) === true;
    }
}
