<?php

namespace App\Modules\GSO\Models;

use App\Core\Models\Department;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ics extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'ics';

    protected $fillable = [
        'ics_number',
        'department_id',
        'fund_source_id',
        'entity_name_snapshot',
        'fund_cluster_code_snapshot',
        'fund_cluster_name_snapshot',
        'issued_date',
        'received_from_name',
        'received_from_position',
        'received_from_office',
        'received_from_date',
        'received_by_name',
        'received_by_position',
        'received_by_office',
        'received_by_date',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'received_from_date' => 'date',
            'received_by_date' => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class, 'fund_source_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(IcsItem::class, 'ics_id', 'id');
    }
}
