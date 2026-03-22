<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\InventoryItemEvent;
use App\Modules\GSO\Repositories\Contracts\InventoryItemEventRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentInventoryItemEventRepository implements InventoryItemEventRepositoryInterface
{
    public function listForInventoryItem(string $inventoryItemId, bool $withTrashed = false): Collection
    {
        $query = InventoryItemEvent::query()
            ->with($this->relations())
            ->where('inventory_item_id', $inventoryItemId)
            ->orderByDesc('event_date')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->get();
    }

    public function getForPropertyCard(string $inventoryItemId): Collection
    {
        return InventoryItemEvent::query()
            ->with([
                'department' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'fundSource' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name', 'fund_cluster_id'])
                    ->with([
                        'fundCluster' => fn ($clusterQuery) => $clusterQuery
                            ->withTrashed()
                            ->select(['id', 'code', 'name']),
                    ]),
            ])
            ->where('inventory_item_id', $inventoryItemId)
            ->orderBy('event_date')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    public function create(array $data): InventoryItemEvent
    {
        return InventoryItemEvent::query()
            ->create($data)
            ->load($this->relations());
    }

    private function relations(): array
    {
        return [
            'department' => fn ($query) => $query
                ->withTrashed()
                ->select(['id', 'code', 'name']),
            'performedBy' => fn ($query) => $query
                ->withTrashed()
                ->select(['id', 'username', 'email']),
            'fundSource' => fn ($query) => $query
                ->withTrashed()
                ->select(['id', 'code', 'name']),
            'files' => fn ($query) => $query
                ->select([
                    'id',
                    'inventory_item_event_id',
                    'disk',
                    'path',
                    'drive_file_id',
                    'drive_web_view_link',
                    'original_name',
                    'mime_type',
                    'size_bytes',
                    'created_at',
                ]),
        ];
    }
}
