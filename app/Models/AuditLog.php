<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'audit_logs';

    protected $fillable = [
        'id','actor_id','actor_type','subject_type','subject_id','action',
        'message','request_method','request_url','ip','user_agent',
        'changes_old','changes_new','meta',
    ];

    protected $casts = [
        'changes_old' => 'array',
        'changes_new' => 'array',
        'meta'        => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (empty($m->id)) {
                $m->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

        // NEW: who did it
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    // Already had subject() earlier; include if missing:
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
