<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelHasPermission extends Model
{
    use HasFactory;

    protected $table = 'model_has_permissions';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'module_id',
        'permission_id',
        'model_type',
        'model_id',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id', 'id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
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
