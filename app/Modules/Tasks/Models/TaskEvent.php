<?php

namespace App\Modules\Tasks\Models;

use App\Core\Models\User;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskEvent extends Model
{
    use HasFactory, SoftDeletes, HasUuid;

    protected $table = 'task_events';

    protected $fillable = [
        'task_id',
        'actor_user_id',
        'actor_name_snapshot',
        'actor_username_snapshot',
        'event_type',
        'from_status',
        'to_status',
        'note',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id', 'id');
    }
}
