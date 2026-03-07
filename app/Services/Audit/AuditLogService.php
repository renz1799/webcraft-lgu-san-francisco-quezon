<?php
// app/Services/Audit/AuditLogService.php
namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Services\Contracts\AuditLogServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLogService implements AuditLogServiceInterface
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $logs
    ) {}

    public function record(
        string $action,
        ?Model $subject = null,
        array $changesOld = [],
        array $changesNew = [],
        array $meta = [],
        ?string $message = null
    ): AuditLog {
        $req = request();

        return $this->logs->create([
            'actor_id'       => optional($req->user())->id,
            'actor_type'     => $req->user() ? get_class($req->user()) : null,
            'subject_type'   => $subject ? get_class($subject) : null,
            'subject_id'     => $subject?->getKey(),
            'action'         => $action,
            'message'        => $message,

            'request_method' => strtoupper($req->method()),
            'request_url'    => Request::fullUrl(),
            'ip'             => $req->ip(),
            'user_agent'     => (string) $req->header('User-Agent'),

            'changes_old'    => $changesOld ?: null,
            'changes_new'    => $changesNew ?: null,
            'meta'           => $meta ?: null,
        ]);
    }

    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        return $this->logs->datatable($filters, $page, $size);
    }

    public function paginate(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        return $this->logs->paginate($perPage, $filters);
    }
}
