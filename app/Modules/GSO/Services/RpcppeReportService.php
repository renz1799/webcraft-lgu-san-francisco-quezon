<?php

namespace App\Modules\GSO\Services;

use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Services\Contracts\RpcppeReportServiceInterface;
use Illuminate\Database\Eloquent\Builder;

class RpcppeReportService extends AbstractPhysicalCountReportService implements RpcppeReportServiceInterface
{
    protected function reportCode(): string
    {
        return 'RPCPPE';
    }

    protected function itemFallbackLabel(): string
    {
        return 'Property Item';
    }

    protected function applyClassificationScope(Builder $query): Builder
    {
        return $query->where(function (Builder $classificationQuery) {
            $classificationQuery->where('is_ics', false)
                ->orWhereNull('is_ics');
        });
    }

    protected function matchesInventoryItem(InventoryItem $inventoryItem): bool
    {
        if ((bool) ($inventoryItem->is_ics ?? false)) {
            return false;
        }

        $trackingType = $this->nullableTrim($inventoryItem->item?->tracking_type);
        if ($trackingType === 'property') {
            return true;
        }

        return $this->nullableTrim($inventoryItem->property_number) !== null;
    }
}
