<?php
// app/Services/Audit/AuditLogService.php
namespace App\Services\AuditLogs;

use App\Models\AuditLog;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Services\Contracts\AuditLogs\AuditLogServiceInterface;
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
        ?string $message = null,
        array $display = [],
    ): AuditLog {
        $req = request();
        $meta = $this->mergeDisplayIntoMeta($meta, $display);

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

    private function mergeDisplayIntoMeta(array $meta, array $display): ?array
    {
        $normalizedDisplay = $this->normalizeDisplayPayload(
            $display ?: (is_array($meta['display'] ?? null) ? $meta['display'] : [])
        );

        if ($normalizedDisplay === []) {
            return $meta ?: null;
        }

        $meta['display'] = $normalizedDisplay;

        return $meta;
    }

    private function normalizeDisplayPayload(array $display): array
    {
        $summary = trim((string) ($display['summary'] ?? ''));
        $subjectLabel = trim((string) ($display['subject_label'] ?? ''));
        $sections = $this->normalizeDisplaySections($display['sections'] ?? []);
        $requestDetails = $this->normalizeDisplayMap($display['request_details'] ?? []);
        $systemNotes = $this->normalizeDisplayNotes($display['system_notes'] ?? []);

        return array_filter([
            'summary' => $summary !== '' ? $summary : null,
            'subject_label' => $subjectLabel !== '' ? $subjectLabel : null,
            'sections' => $sections,
            'request_details' => $requestDetails,
            'system_notes' => $systemNotes,
        ], static fn (mixed $value): bool => $value !== null && $value !== []);
    }

    private function normalizeDisplaySections(mixed $sections): array
    {
        if (! is_array($sections)) {
            return [];
        }

        $normalized = [];

        foreach ($sections as $section) {
            if (! is_array($section)) {
                continue;
            }

            $title = trim((string) ($section['title'] ?? ''));
            $items = $this->normalizeDisplayItems($section['items'] ?? []);

            if ($title === '' || $items === []) {
                continue;
            }

            $normalized[] = [
                'title' => $title,
                'items' => $items,
            ];
        }

        return $normalized;
    }

    private function normalizeDisplayItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $label = trim((string) ($item['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $row = ['label' => $label];

            if (array_key_exists('value', $item)) {
                $row['value'] = $item['value'];
            }

            if (array_key_exists('before', $item)) {
                $row['before'] = $item['before'];
            }

            if (array_key_exists('after', $item)) {
                $row['after'] = $item['after'];
            }

            if (count($row) === 1) {
                continue;
            }

            $normalized[] = $row;
        }

        return $normalized;
    }

    private function normalizeDisplayMap(mixed $details): array
    {
        if (! is_array($details)) {
            return [];
        }

        $normalized = [];

        foreach ($details as $label => $value) {
            $key = trim((string) $label);
            if ($key === '' || $value === null || $value === '' || $value === []) {
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private function normalizeDisplayNotes(mixed $notes): array
    {
        if (! is_array($notes)) {
            return [];
        }

        $normalized = [];

        foreach ($notes as $note) {
            if (! is_array($note)) {
                continue;
            }

            $title = trim((string) ($note['title'] ?? ''));
            $items = is_array($note['items'] ?? null)
                ? array_values(array_filter($note['items'], static fn (mixed $item): bool => $item !== null && $item !== '' && $item !== []))
                : [];

            if ($title === '' || $items === []) {
                continue;
            }

            $normalized[] = [
                'title' => $title,
                'items' => $items,
            ];
        }

        return $normalized;
    }
}
