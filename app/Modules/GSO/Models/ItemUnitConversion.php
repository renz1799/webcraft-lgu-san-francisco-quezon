<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemUnitConversion extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'item_unit_conversions';

    protected $fillable = [
        'item_id',
        'from_unit',
        'multiplier',
    ];

    protected function casts(): array
    {
        return [
            'multiplier' => 'integer',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}
