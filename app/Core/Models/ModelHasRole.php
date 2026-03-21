<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelHasRole extends Model
{
    use HasFactory;

    protected $table = 'model_has_roles';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'module_id',
        'role_id',
        'model_type',
        'model_id',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id', 'id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForModule($query, string $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }
}
