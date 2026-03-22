<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockMovement extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'stock_movements';

    protected $fillable = [
        'item_id',
        'fund_source_id',
        'movement_type',
        'qty',
        'reference_type',
        'reference_id',
        'air_item_id',
        'ris_item_id',
        'occurred_at',
        'created_by_name',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class, 'fund_source_id', 'id');
    }
}
