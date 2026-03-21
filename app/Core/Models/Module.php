<?php

namespace App\Core\Models;

use Database\Factories\ModuleFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'description',
        'url',
        'default_department_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): Factory
    {
        return ModuleFactory::new();
    }

    public function userModules(): HasMany
    {
        return $this->hasMany(UserModule::class, 'module_id', 'id');
    }

    public function defaultDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'default_department_id', 'id');
    }

    public function googleTokens(): HasMany
    {
        return $this->hasMany(GoogleToken::class, 'module_id', 'id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'module_id', 'id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'module_id', 'id');
    }

    public function loginDetails(): HasMany
    {
        return $this->hasMany(LoginDetail::class, 'module_id', 'id');
    }

    public function appSettings(): HasMany
    {
        return $this->hasMany(AppSetting::class, 'module_id', 'id');
    }
}
