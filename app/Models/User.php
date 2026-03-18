<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasUuid, SoftDeletes;

    protected $fillable = [
        'username',
        'email',
        'password',
        'must_change_password',
        'user_type',
        'is_active',
        'email_verified_at',
        'primary_department_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'must_change_password' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::bootHasUuid();
    }

    public function primaryDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'primary_department_id', 'id');
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    public function loginDetails()
    {
        return $this->hasMany(LoginDetail::class, 'user_id', 'id');
    }

    public function userModules(): HasMany
    {
        return $this->hasMany(UserModule::class, 'user_id', 'id');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by_user_id', 'id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to_user_id', 'id');
    }

    public function receivedNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'notifiable_user_id', 'id');
    }

    public function triggeredNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'actor_user_id', 'id');
    }
}