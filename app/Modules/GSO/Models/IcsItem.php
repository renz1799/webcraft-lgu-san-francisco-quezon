<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IcsItem extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'ics_items';

    protected $fillable = [
        'ics_id',
        'inventory_item_id',
        'line_no',
        'quantity',
        'unit_snapshot',
        'unit_cost_snapshot',
        'total_cost_snapshot',
        'description_snapshot',
        'inventory_item_no_snapshot',
        'estimated_useful_life_snapshot',
        'item_name_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'line_no' => 'integer',
            'quantity' => 'integer',
            'unit_cost_snapshot' => 'decimal:2',
            'total_cost_snapshot' => 'decimal:2',
        ];
    }

    public function ics(): BelongsTo
    {
        return $this->belongsTo(Ics::class, 'ics_id', 'id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id', 'id');
    }
}
