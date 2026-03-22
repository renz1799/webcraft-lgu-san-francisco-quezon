<?php

namespace App\Modules\GSO\Builders;

use App\Modules\GSO\Builders\Contracts\StockDatatableRowBuilderInterface;
use App\Modules\GSO\Models\Item;
use Carbon\Carbon;

class StockDatatableRowBuilder implements StockDatatableRowBuilderInterface
{
    public function build(Item $item): array
    {
        $stocks = $item->stocks ?? collect();
        $funds = $stocks
            ->map(function ($stock): array {
                $fundSource = $stock->fundSource;
                $code = trim((string) ($fundSource?->code ?? ''));
                $name = trim((string) ($fundSource?->name ?? ''));
                $label = $code !== ''
                    ? trim($code . ($name !== '' ? ' - ' . $name : ''))
                    : 'Unassigned Stock Row';

                return [
                    'id' => $fundSource?->id ? (string) $fundSource->id : '',
                    'code' => $code,
                    'name' => $name,
                    'label' => $label,
                    'on_hand' => (int) ($stock->on_hand ?? 0),
                    'fund_cluster_label' => $this->buildFundClusterLabel(
                        $fundSource?->fundCluster?->code,
                        $fundSource?->fundCluster?->name,
                    ),
                ];
            })
            ->values()
            ->all();

        $lastMovementAt = $item->getAttribute('last_movement_at');

        return [
            'id' => (string) $item->id,
            'item_id' => (string) $item->id,
            'item_name' => (string) ($item->item_name ?? ''),
            'stock_number' => (string) ($item->item_identification ?? ''),
            'description' => (string) ($item->description ?? ''),
            'unit' => (string) ($item->base_unit ?? ''),
            'on_hand' => (int) ($item->getAttribute('on_hand_total') ?? 0),
            'funds' => $funds,
            'fund_count' => count($funds),
            'has_stock_rows' => count($funds) > 0,
            'last_movement_at' => $lastMovementAt
                ? Carbon::parse((string) $lastMovementAt)->format('M j, Y g:i A')
                : '',
            'last_movement_sort' => $lastMovementAt ? (string) $lastMovementAt : '',
            'is_archived' => (bool) $item->trashed(),
        ];
    }

    private function buildFundClusterLabel(?string $code, ?string $name): string
    {
        $code = trim((string) $code);
        $name = trim((string) $name);

        if ($code !== '' && $name !== '') {
            return sprintf('%s - %s', $code, $name);
        }

        return $code !== '' ? $code : $name;
    }
}
