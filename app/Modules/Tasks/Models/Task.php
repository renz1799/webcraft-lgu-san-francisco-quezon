<?php

namespace App\Modules\Tasks\Models;

use App\Core\Models\Department;
use App\Core\Models\Module;
use App\Core\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'module_id',
        'department_id',
        'title',
        'description',
        'type',
        'status',
        'priority',
        'created_by_user_id',
        'assigned_to_user_id',
        'subject_type',
        'subject_id',
        'due_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'due_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $appends = [
        'assigned_to_name',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TaskEvent::class, 'task_id')->orderBy('created_at');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id', 'id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id', 'id');
    }

    public function getAssignedToNameAttribute(): string
    {
        $user = $this->assignee;

        if (! $user) {
            return '-';
        }

        return $user->profile?->full_name
            ?: ($user->username ?: '-');
    }
}
