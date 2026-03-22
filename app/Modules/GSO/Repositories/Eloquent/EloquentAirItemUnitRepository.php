<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\AirItemUnit;
use App\Modules\GSO\Repositories\Contracts\AirItemUnitRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentAirItemUnitRepository implements AirItemUnitRepositoryInterface
{
    public function listForAirItem(string $airItemId, bool $withTrashed = false): Collection
    {
        $query = $this->baseQuery()
            ->where('air_item_id', $airItemId)
            ->orderBy('created_at')
            ->orderBy('id');

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->get();
    }

    public function findForAirItemOrFail(string $airItemId, string $unitId, bool $withTrashed = false): AirItemUnit
    {
        $query = $this->baseQuery()
            ->where('air_item_id', $airItemId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($unitId);
    }

    public function countByAirItemIds(array $airItemIds): array
    {
        if ($airItemIds === []) {
            return [];
        }

        return AirItemUnit::query()
            ->selectRaw('air_item_id, count(*) as aggregate')
            ->whereIn('air_item_id', $airItemIds)
            ->whereNull('deleted_at')
            ->groupBy('air_item_id')
            ->pluck('aggregate', 'air_item_id')
            ->map(fn (mixed $count): int => (int) $count)
            ->all();
    }

    public function create(array $data): AirItemUnit
    {
        return AirItemUnit::query()
            ->create($data)
            ->load($this->relations())
            ->loadCount(['files', 'components']);
    }

    public function save(AirItemUnit $unit): AirItemUnit
    {
        $unit->save();

        return $unit->refresh()
            ->load($this->relations())
            ->loadCount(['files', 'components']);
    }

    public function delete(AirItemUnit $unit): void
    {
        $unit->delete();
    }

    private function baseQuery()
    {
        return AirItemUnit::query()
            ->with($this->relations())
            ->withCount(['files', 'components']);
    }

    private function relations(): array
    {
        return [
            'inventoryItem' => fn ($query) => $query
                ->withTrashed()
                ->select([
                    'id',
                    'item_id',
                    'property_number',
                    'serial_number',
                    'accountable_officer',
                    'accountable_officer_id',
                    'status',
                    'condition',
                ]),
            'components' => fn ($query) => $query
                ->select([
                    'id',
                    'air_item_unit_id',
                    'line_no',
                    'name',
                    'quantity',
                    'unit',
                    'component_cost',
                    'serial_number',
                    'condition',
                    'is_present',
                    'remarks',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ]),
        ];
    }
}
