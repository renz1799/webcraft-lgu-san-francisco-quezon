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
                'actor:id,username,email',
                'subject' => function (MorphTo $morph) {
                    $morph->constrain([
                        \App\Models\User::class        => fn ($q) => $q->withTrashed()->select('id','username','email'),
                        \App\Models\Permission::class  => fn ($q) => $q->withTrashed()->select('id','name','page'),
                    ]);
                },
            ])
            ->latest('created_at');

        if (!is_null($filters['action'] ?? null)) {
            $q->where('action', 'like', '%'.$filters['action'].'%');
        }
        if (!is_null($filters['actor_id'] ?? null)) {
            $q->where('actor_id', $filters['actor_id']);
        }
        if (!is_null($filters['date_from'] ?? null)) {
            $q->where('created_at', '>=', \Carbon\Carbon::parse($filters['date_from'])->startOfDay());
        }
        if (!is_null($filters['date_to'] ?? null)) {
            $q->where('created_at', '<=', \Carbon\Carbon::parse($filters['date_to'])->endOfDay());
        }

        return $q->paginate($perPage)->withQueryString();
    }

    public function paginateForTable(array $filters, int $page, int $size): LengthAwarePaginator
    {
        $q = AuditLog::query()
            ->with(['actor', 'subject']) // subject should already be withTrashed in relation if you configured it
            ->latest();

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

        return $q->paginate($size, ['*'], 'page', $page);
    }
}
