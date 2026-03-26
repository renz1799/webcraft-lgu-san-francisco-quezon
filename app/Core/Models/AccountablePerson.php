<?php

namespace App\Core\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountablePerson extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'accountable_officers';

    protected $fillable = [
        'full_name',
        'normalized_name',
        'designation',
        'office',
        'department_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}
