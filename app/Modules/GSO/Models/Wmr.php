<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wmr extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'wmrs';

    protected $fillable = [
        'wmr_number',
        'fund_cluster_id',
        'entity_name_snapshot',
        'fund_cluster_code_snapshot',
        'place_of_storage',
        'report_date',
        'status',
        'remarks',
        'custodian_name',
        'custodian_designation',
        'custodian_date',
        'approved_by_name',
        'approved_by_designation',
        'approved_by_date',
        'inspection_officer_name',
        'inspection_officer_designation',
        'inspection_officer_date',
        'witness_name',
        'witness_designation',
        'witness_date',
    ];

    protected $casts = [
        'report_date' => 'date',
        'custodian_date' => 'date',
        'approved_by_date' => 'date',
        'inspection_officer_date' => 'date',
        'witness_date' => 'date',
    ];

    public function fundCluster(): BelongsTo
    {
        return $this->belongsTo(FundCluster::class, 'fund_cluster_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WmrItem::class, 'wmr_id');
    }
}

