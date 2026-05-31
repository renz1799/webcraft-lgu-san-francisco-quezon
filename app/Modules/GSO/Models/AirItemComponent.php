<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirItemComponent extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $table = 'air_item_components';

    protected $fillable = [
        'air_item_id',
        'client_request_id',
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

    public function airItem(): BelongsTo
    {
        return $this->belongsTo(AirItem::class, 'air_item_id', 'id');
    }
}
