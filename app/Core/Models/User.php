<?php

namespace App\Core\Models;

use App\Core\Notifications\Auth\CorePasswordResetNotification;
use App\Core\Notifications\Auth\ModuleAccessGrantedNotification;
use App\Core\Notifications\Auth\UserInvitationNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, HasUuids;

    protected $table = 'users';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'primary_department_id',
        'username',
        'email',
        'password',
        'must_change_password',
        'is_active',
        'last_login_at',
        'user_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    public function loginDetails(): HasMany
    {
        return $this->hasMany(LoginDetail::class, 'user_id', 'id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'notifiable_user_id', 'id');
    }

    public function primaryDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'primary_department_id', 'id');
    }

    public function userModules(): HasMany
    {
        return $this->hasMany(UserModule::class, 'user_id', 'id');
    }

    public function moduleRoleAssignments(): HasMany
    {
        return $this->hasMany(ModelHasRole::class, 'model_id', 'id')
            ->where('model_type', self::class);
    }

    public function identityChangeRequests(): HasMany
    {
        return $this->hasMany(UserIdentityChangeRequest::class, 'user_id', 'id');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CorePasswordResetNotification($token));
    }

    public function sendUserInvitationNotification(
        string $token,
        string $moduleName,
        ?string $departmentName = null,
        ?string $roleName = null,
    ): void {
        $this->notify(new UserInvitationNotification($token, $moduleName, $departmentName, $roleName));
    }

    public function sendModuleAccessGrantedNotification(
        string $moduleName,
        ?string $departmentName = null,
        ?string $roleName = null,
        bool $isActive = true,
    ): void {
        $this->notify(new ModuleAccessGrantedNotification($moduleName, $departmentName, $roleName, $isActive));
    }

    public function shouldReceiveOnboardingInvitation(): bool
    {
        return (bool) $this->must_change_password;
    }
}
