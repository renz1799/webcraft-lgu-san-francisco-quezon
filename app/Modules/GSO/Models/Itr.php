<?php

namespace App\Modules\GSO\Models;

use App\Core\Models\Department;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Itr extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'itrs';

    protected $fillable = [
        'itr_number',
        'transfer_date',
        'status',
        'from_department_id',
        'from_accountable_officer',
        'from_fund_source_id',
        'to_department_id',
        'to_accountable_officer',
        'to_fund_source_id',
        'transfer_type',
        'transfer_type_other',
        'reason_for_transfer',
        'remarks',
        'entity_name_snapshot',
        'header_fund_cluster_code_snapshot',
        'from_department_snapshot',
        'from_fund_cluster_code_snapshot',
        'to_department_snapshot',
        'to_fund_cluster_code_snapshot',
        'approved_by_name',
        'approved_by_designation',
        'approved_by_date',
        'released_by_name',
        'released_by_designation',
        'released_by_date',
        'received_by_name',
        'received_by_designation',
        'received_by_date',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'approved_by_date' => 'date',
        'released_by_date' => 'date',
        'received_by_date' => 'date',
    ];

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function fromFundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class, 'from_fund_source_id');
    }

    public function toFundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class, 'to_fund_source_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItrItem::class, 'itr_id');
    }
}



