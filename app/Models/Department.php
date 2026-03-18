<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'short_name',
        'type',
        'parent_department_id',
        'head_user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parentDepartment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_department_id', 'id');
    }

    public function childDepartments(): HasMany
    {
        return $this->hasMany(self::class, 'parent_department_id', 'id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'primary_department_id', 'id');
    }

    public function userModules(): HasMany
    {
        return $this->hasMany(UserModule::class, 'department_id', 'id');
    }

    public function googleTokens(): HasMany
    {
        return $this->hasMany(GoogleToken::class, 'department_id', 'id');
    }
}