<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirItemUnitComponent extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'air_item_unit_components';

    protected $fillable = [
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
    ];

    protected function casts(): array
    {
        return [
            'line_no' => 'integer',
            'quantity' => 'integer',
            'component_cost' => 'decimal:2',
            'is_present' => 'boolean',
        ];
    }

    public function airItemUnit(): BelongsTo
    {
        return $this->belongsTo(AirItemUnit::class, 'air_item_unit_id', 'id');
    }
}
