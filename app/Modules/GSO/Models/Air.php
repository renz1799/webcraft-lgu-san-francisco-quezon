<?php

namespace App\Modules\GSO\Models;

use App\Core\Models\Department;
use App\Core\Models\User;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Air extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'airs';

    protected $fillable = [
        'parent_air_id',
        'continuation_no',
        'po_number',
        'po_date',
        'air_number',
        'air_date',
        'invoice_number',
        'invoice_date',
        'supplier_name',
        'requesting_department_id',
        'requesting_department_name_snapshot',
        'requesting_department_code_snapshot',
        'fund_source_id',
        'fund',
        'status',
        'date_received',
        'received_completeness',
        'received_notes',
        'date_inspected',
        'inspection_verified',
        'inspection_notes',
        'inspected_by_name',
        'accepted_by_name',
        'drive_folder_id',
        'created_by_user_id',
        'created_by_name_snapshot',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'continuation_no' => 'integer',
            'po_date' => 'date',
            'air_date' => 'date',
            'invoice_date' => 'date',
            'date_received' => 'date',
            'date_inspected' => 'date',
            'inspection_verified' => 'boolean',
        ];
    }

    public function parentAir(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_air_id', 'id');
    }

    public function followUpAirs(): HasMany
    {
        return $this->hasMany(self::class, 'parent_air_id', 'id')
            ->orderBy('continuation_no')
            ->orderBy('created_at');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'requesting_department_id', 'id');
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class, 'fund_source_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AirItem::class, 'air_id', 'id')
            ->orderBy('item_name_snapshot')
            ->orderBy('created_at');
    }

    public function files(): HasMany
    {
        return $this->hasMany(AirFile::class, 'air_id', 'id')
            ->orderByDesc('is_primary')
            ->orderBy('position')
            ->orderByDesc('created_at');
    }
}
