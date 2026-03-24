<?php

namespace Database\Factories\Modules\GSO;

use App\Modules\GSO\Models\AirItem;
use App\Modules\GSO\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class AirItemFactory extends Factory
{
    protected $model = AirItem::class;

    public function definition(): array
    {
        $qtyOrdered = $this->faker->numberBetween(1, 50);
        $qtyDelivered = $this->faker->numberBetween(0, $qtyOrdered);
        $qtyAccepted = $this->faker->numberBetween(0, $qtyDelivered);

        $item = Item::query()->inRandomOrder()->first();

        return [
            'item_id' => $item?->id,

            'stock_no_snapshot' => $item?->item_identification,
            'item_name_snapshot' => $item?->item_name ?? $this->faker->words(3, true),
            'description_snapshot' => $item?->description ?? $this->faker->sentence(8),
            'unit_snapshot' => $item?->base_unit ?? $this->faker->randomElement(['piece', 'unit', 'ream', 'box']),

            'acquisition_cost' => $this->faker->randomFloat(2, 50, 50000),

            'qty_ordered' => $qtyOrdered,
            'qty_delivered' => $qtyDelivered,
            'qty_accepted' => $qtyAccepted,

            'tracking_type_snapshot' => $item?->tracking_type ?? $this->faker->randomElement(['consumable', 'property']),
            'requires_serial_snapshot' => (bool) ($item?->requires_serial ?? false),
            'is_semi_expendable_snapshot' => (bool) ($item?->is_semi_expendable ?? false),

            'remarks' => null,
        ];
    }
}