<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditLog extends Model
{
    use HasUuids;

    protected $table = 'audit_logs';

    protected $fillable = [
        'module_id',
        'department_id',
        'actor_id',
        'actor_type',
        'subject_type',
        'subject_id',
        'action',
        'message',
        'request_method',
        'request_url',
        'ip',
        'user_agent',
        'changes_old',
        'changes_new',
        'meta',
    ];

    protected $casts = [
        'changes_old' => 'array',
        'changes_new' => 'array',
        'meta' => 'array',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }
}