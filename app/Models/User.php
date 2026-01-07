<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;   // <-- add
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasUuid, SoftDeletes; // <-- include

    protected $fillable = [
        'username',
        'email',
        'password',
        'must_change_password',
        'user_type',
        'is_active',
        'email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'must_change_password' => 'boolean',
        'is_active'         => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::bootHasUuid();
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    public function loginDetails()
    {
        return $this->hasMany(LoginDetail::class, 'user_id', 'id');
    }
}
