<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AirItem extends Model
{
    use HasUuid;

    protected $table = 'air_items';

    protected $fillable = [
        'air_id',
        'item_id',
        'stock_no_snapshot',
        'item_name_snapshot',
        'description_snapshot',
        'unit_snapshot',
        'acquisition_cost',
        'qty_ordered',
        'qty_delivered',
        'qty_accepted',
        'tracking_type_snapshot',
        'requires_serial_snapshot',
        'is_semi_expendable_snapshot',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_cost' => 'decimal:2',
            'qty_ordered' => 'integer',
            'qty_delivered' => 'integer',
            'qty_accepted' => 'integer',
            'requires_serial_snapshot' => 'boolean',
            'is_semi_expendable_snapshot' => 'boolean',
        ];
    }

    public function air(): BelongsTo
    {
        return $this->belongsTo(Air::class, 'air_id', 'id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(AirItemUnit::class, 'air_item_id', 'id')
            ->orderBy('created_at')
            ->orderBy('id');
    }
}
