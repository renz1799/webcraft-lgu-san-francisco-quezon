<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'stocks';

    protected $fillable = [
        'item_id',
        'fund_source_id',
        'on_hand',
    ];

    protected function casts(): array
    {
        return [
            'on_hand' => 'integer',
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

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'item_id', 'item_id');
    }
}
