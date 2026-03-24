<?php

namespace App\Modules\GSO\Models;

use App\Core\Models\Department;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ris extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'ris';

    protected $fillable = [
        'air_id',
        'ris_number',
        'ris_date',
        'requesting_department_id',
        'requesting_department_name_snapshot',
        'requesting_department_code_snapshot',
        'fund',
        'fund_source_id',
        'fpp_code',
        'division',
        'responsibility_center_code',
        'status',
        'submitted_by_name',
        'submitted_at',
        'rejected_by_name',
        'rejected_at',
        'rejected_reason',
        'requested_by_name',
        'requested_by_designation',
        'requested_by_date',
        'approved_by_name',
        'approved_by_designation',
        'approved_by_date',
        'issued_by_name',
        'issued_by_designation',
        'issued_by_date',
        'received_by_name',
        'received_by_designation',
        'received_by_date',
        'purpose',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'ris_date' => 'date',
            'requested_by_date' => 'date',
            'approved_by_date' => 'date',
            'issued_by_date' => 'date',
            'received_by_date' => 'date',
            'submitted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function air(): BelongsTo
    {
        return $this->belongsTo(Air::class, 'air_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'requesting_department_id', 'id');
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class, 'fund_source_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RisItem::class, 'ris_id', 'id')
            ->orderBy('line_no')
            ->orderBy('created_at');
    }

    public function files(): HasMany
    {
        return $this->hasMany(RisFile::class, 'ris_id', 'id')
            ->orderByDesc('is_primary')
            ->orderBy('position')
            ->orderByDesc('created_at');
    }
}
