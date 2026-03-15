<?php
// app/Services/Contracts/AuditLogServiceInterface.php
namespace App\Services\Contracts;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface AuditLogServiceInterface
{
    /**
     * Record an audit log entry.
     *
     * Phase 2 display payloads are stored under meta.display without changing the table schema.
     * Expected shape:
     * [
     *   'summary' => 'Permissions updated for Craig Scot Schamberger',
     *   'subject_label' => 'Craig Scot Schamberger',
     *   'sections' => [
     *     [
     *       'title' => 'Direct Permissions',
     *       'items' => [
     *         ['label' => 'Added', 'value' => ['Modify Tasks', 'Delete Tasks']],
     *         ['label' => 'Removed', 'value' => []],
     *       ],
     *     ],
     *   ],
     *   'request_details' => [
     *     'Reference No' => 'PO-2026-0312',
     *   ],
     *   'system_notes' => [
     *     [
     *       'title' => 'Resolved selections',
     *       'items' => ['Manage Tasks / Tasks / Edit'],
     *     ],
     *   ],
     * ]
     *
     * Existing callers remain valid. New callers may pass the structured payload through the
     * optional $display argument, or directly in meta['display'] if they need manual control.
     */
    public function record(
        string $action,
        ?Model $subject = null,
        array $changesOld = [],
        array $changesNew = [],
        array $meta = [],
        ?string $message = null,
        array $display = [],
    ): AuditLog;

    public function datatable(array $params): array;

    public function paginate(int $perPage = 50, array $filters = []): LengthAwarePaginator;
}
