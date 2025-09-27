<?php
// app/Repositories/Eloquent/EloquentAuditLogRepository.php
namespace App\Repositories\Eloquent;

use App\Models\AuditLog;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog
    {
        return AuditLog::create($data);
    }

    public function paginate(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
            $q = \App\Models\AuditLog::query()
        ->with(['actor', 'subject'])   // <— important
        ->latest('created_at');

        if (!empty($filters['action'])) {
            $q->where('action', 'like', '%'.$filters['action'].'%');
        }
        if (!empty($filters['actor_id'])) {
            $q->where('actor_id', $filters['actor_id']);
        }
        if (!empty($filters['date_from'])) {
            $q->where('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $q->where('created_at', '<=', $filters['date_to']);
        }

        return $q->paginate($perPage)->withQueryString();
    }
}
