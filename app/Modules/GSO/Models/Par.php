<?php

namespace App\Modules\GSO\Models;

use App\Core\Models\Department;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Par extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'pars';

    protected $fillable = [
        'par_number',
        'department_id',
        'fund_source_id',
        'person_accountable',
        'received_by_position',
        'received_by_date',
        'issued_by_name',
        'issued_by_position',
        'issued_by_office',
        'issued_by_date',
        'issued_date',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'received_by_date' => 'date',
            'issued_by_date' => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ParItem::class, 'par_id', 'id');
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class, 'fund_source_id', 'id');
    }
}
