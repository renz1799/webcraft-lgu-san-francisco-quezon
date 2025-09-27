<?php
// app/Repositories/Eloquent/EloquentAuditLogRepository.php

namespace App\Repositories\Eloquent;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Permission;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog
    {
        return AuditLog::create($data);
    }

    public function paginate(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $q = AuditLog::query()
            ->with([
                // Only what the view needs
                'actor:id,username,email',
                // Load the polymorphic subject, including soft-deleted rows,
                // and only select the columns you display in the table.
                'subject' => function (MorphTo $morph) {
                    $morph->constrain([
                        User::class => fn ($q) => $q->withTrashed()->select('id','username','email'),
                        Permission::class => fn ($q) => $q->withTrashed()->select('id','name','page'),
                        // Add other subject types here as needed...
                    ]);
                },
            ])
            ->latest('created_at');

        if (!empty($filters['action'])) {
            $q->where('action', 'like', '%'.$filters['action'].'%');
        }
        if (!empty($filters['actor_id'])) {
            $q->where('actor_id', $filters['actor_id']);
        }
        if (!empty($filters['date_from'])) {
            $q->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $q->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $q->paginate($perPage)->withQueryString();
    }
}
