<?php

namespace App\Modules\GSO\Models;

use App\Core\Models\Department;
use App\Core\Models\User;
use App\Models\Concerns\HasUuid;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inspection extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'inspections';

    protected $fillable = [
        'inspector_user_id',
        'reviewer_user_id',
        'status',
        'department_id',
        'item_id',
        'office_department',
        'accountable_officer',
        'dv_number',
        'po_number',
        'observed_description',
        'item_name',
        'brand',
        'model',
        'serial_number',
        'acquisition_cost',
        'acquisition_date',
        'quantity',
        'condition',
        'drive_folder_id',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'acquisition_cost' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function setAcquisitionDateAttribute(mixed $value): void
    {
        if ($value === null || trim((string) $value) === '') {
            $this->attributes['acquisition_date'] = null;

            return;
        }

        $date = $value instanceof CarbonInterface
            ? $value
            : Carbon::parse((string) $value);

        $this->attributes['acquisition_date'] = $date->toDateString();
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_user_id', 'id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(InspectionPhoto::class, 'inspection_id', 'id');
    }
}
