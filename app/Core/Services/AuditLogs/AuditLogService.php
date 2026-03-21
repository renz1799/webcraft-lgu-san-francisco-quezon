<?php
// app/Services/Audit/AuditLogService.php
namespace App\Core\Services\AuditLogs;

use App\Core\Builders\Contracts\AuditLogs\AuditLogMetaBuilderInterface;
use App\Core\Models\AuditLog;
use App\Core\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Support\AuditRequestContextResolver;
use App\Core\Support\CurrentContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class AuditLogService implements AuditLogServiceInterface
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $logs,
        private readonly CurrentContext $context,
        private readonly AuditLogMetaBuilderInterface $metaBuilder,
        private readonly AuditRequestContextResolver $requestContextResolver,
    ) {}

    public function record(
        string $action,
        ?Model $subject = null,
        array $changesOld = [],
        array $changesNew = [],
        array $meta = [],
        ?string $message = null,
        array $display = [],
    ): AuditLog {
        $meta = $this->metaBuilder->build($meta, $display);
        $requestContext = $this->requestContextResolver->resolve();

        return $this->logs->create([
            'module_id'      => $this->context->moduleId(),
            'department_id'  => $this->context->defaultDepartmentId(),
            'actor_id'       => $requestContext['actor_id'],
            'actor_type'     => $requestContext['actor_type'],
            'subject_type'   => $subject ? get_class($subject) : null,
            'subject_id'     => $subject?->getKey(),
            'action'         => $action,
            'message'        => $message,
            'request_method' => $requestContext['request_method'],
            'request_url'    => $requestContext['request_url'],
            'ip'             => $requestContext['ip'],
            'user_agent'     => $requestContext['user_agent'],
            'changes_old'    => $changesOld ?: null,
            'changes_new'    => $changesNew ?: null,
            'meta'           => $meta ?: null,
        ]);
    }

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));

        $filters = $params;
        unset($filters['page'], $filters['size']);

        return $this->logs->datatable($filters, $page, $size);
    }

    public function paginate(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        return $this->logs->paginate($perPage, $filters);
    }
}
