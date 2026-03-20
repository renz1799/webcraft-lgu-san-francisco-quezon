<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'description',
        'url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function userModules(): HasMany
    {
        return $this->hasMany(UserModule::class, 'module_id', 'id');
    }

    public function googleTokens(): HasMany
    {
        return $this->hasMany(GoogleToken::class, 'module_id', 'id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'module_id', 'id');
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
