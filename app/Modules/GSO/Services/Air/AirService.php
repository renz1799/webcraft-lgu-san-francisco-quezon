<?php

namespace App\Modules\GSO\Services\Air;

use App\Core\Models\Department;
use App\Core\Models\Tasks\Task;
use App\Core\Models\User;
use App\Core\Services\Contracts\AccountablePersons\AccountablePersonServiceInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\Notifications\NotificationServiceInterface;
use App\Core\Services\Contracts\Notifications\WorkflowNotificationSettingsServiceInterface;
use App\Core\Services\Tasks\Contracts\TaskServiceInterface;
use App\Modules\GSO\Builders\Contracts\Air\AirDatatableRowBuilderInterface;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\AirItem;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Repositories\Contracts\AirRepositoryInterface;
use App\Modules\GSO\Services\Contracts\Air\AirServiceInterface;
use App\Modules\GSO\Support\Air\AirStatuses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AirService implements AirServiceInterface
{
    public function __construct(
        private readonly AirRepositoryInterface $airs,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly AirDatatableRowBuilderInterface $datatableRowBuilder,
        private readonly AccountablePersonServiceInterface $accountablePersons,
        private readonly TaskServiceInterface $tasks,
        private readonly NotificationServiceInterface $notifications,
        private readonly WorkflowNotificationSettingsServiceInterface $workflowNotifications,
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));
        $paginator = $this->airs->paginateForTable($params, $page, $size);

        return [
            'data' => $paginator->getCollection()
                ->map(fn (Air $air) => $this->datatableRowBuilder->build($air))
                ->values()
                ->all(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    public function getForEdit(string $airId): array
    {
        $air = $this->airs->findOrFail($airId, true);

        return $this->datatableRowBuilder->build($air);
    }

    public function createBlankDraft(string $actorUserId): Air
    {
        return DB::transaction(function () use ($actorUserId) {
            $actor = User::query()
                ->with(['primaryDepartment' => fn ($query) => $query->withTrashed()])
                ->find($actorUserId);
            $defaultDepartment = $this->resolveDefaultDepartment($actor);
            $defaultFundSource = $this->resolveDefaultFundSource();

            $air = $this->airs->create([
                'parent_air_id' => null,
                'continuation_no' => 1,
                'po_number' => $this->generatePlaceholderPoNumber(),
                'po_date' => now()->toDateString(),
                'air_number' => null,
                'air_date' => now()->toDateString(),
                'invoice_number' => null,
                'invoice_date' => null,
                'supplier_name' => 'TBD',
                'requesting_department_id' => $defaultDepartment?->id,
                'requesting_department_name_snapshot' => $defaultDepartment?->name,
                'requesting_department_code_snapshot' => $defaultDepartment?->code,
                'fund_source_id' => $defaultFundSource?->id,
                'fund' => $defaultFundSource?->name,
                'status' => AirStatuses::DRAFT,
                'inspected_by_name' => 'TBD',
                'accepted_by_name' => 'TBD',
                'created_by_user_id' => $actorUserId,
                'created_by_name_snapshot' => $this->resolveActorLabel($actor),
                'remarks' => null,
            ]);

            $this->auditLogs->record(
                action: 'gso.air.created',
                subject: $air,
                changesOld: [],
                changesNew: $air->only([
                    'po_number',
                    'po_date',
                    'air_date',
                    'supplier_name',
                    'requesting_department_id',
                    'fund_source_id',
                    'status',
                ]),
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO AIR draft created: ' . $this->airLabel($air),
                display: $this->buildLifecycleDisplay(
                    summary: 'AIR draft created',
                    air: $air,
                    beforeStatus: 'None',
                    afterStatus: AirStatuses::label(AirStatuses::DRAFT),
                ),
            );

            return $this->airs->findOrFail((string) $air->id, true);
        });
    }

    public function updateDraft(string $actorUserId, string $airId, array $data): Air
    {
        return DB::transaction(function () use ($actorUserId, $airId, $data) {
            $air = $this->airs->findOrFail($airId, true);
            $this->assertEditableDraft($air);

            $before = $this->snapshotAuditFields($air);
            $department = $this->resolveDepartmentForUpdate($data, $air);
            $payload = $this->normalizedPayload($data, $air, $department);

            $this->assertPoNumberAvailable(
                poNumber: (string) ($payload['po_number'] ?? ''),
                ignoreAirId: (string) $air->id,
                rootOnly: $air->parent_air_id === null,
            );

            $air->fill($payload);
            $air = $this->airs->save($air);
            $this->ensureSignatoryRecords($actorUserId, $air);
            $after = $this->snapshotAuditFields($air);

            $this->auditLogs->record(
                action: 'gso.air.updated',
                subject: $air,
                changesOld: $before,
                changesNew: $after,
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO AIR draft updated: ' . $this->airLabel($air),
                display: $this->buildUpdatedDisplay($before, $after),
            );

            return $this->airs->findOrFail((string) $air->id, true);
        });
    }

    public function submitDraft(string $actorUserId, string $airId): Air
    {
        return DB::transaction(function () use ($actorUserId, $airId) {
            $air = $this->airs->findOrFail($airId, true);
            $this->assertEditableDraft($air);
            $air = $this->reconcileLegacyFundSource($air);
            $this->assertHeaderComplete($air);
            $this->assertDraftHasItems($air);
            $this->assertDraftItemUnitsValid($air);

            $poNumber = trim((string) ($air->po_number ?? ''));
            if (str_starts_with(strtoupper($poNumber), 'PO-DRAFT-')) {
                throw ValidationException::withMessages([
                    'po_number' => ['Replace the placeholder PO number before submitting this AIR.'],
                ]);
            }

            $before = $this->snapshotAuditFields($air);
            if (trim((string) ($air->air_number ?? '')) === '') {
                $air->air_number = $this->generateAirNumber();
            }

            $air->status = AirStatuses::SUBMITTED;
            $air = $this->airs->save($air);
            $this->ensureSignatoryRecords($actorUserId, $air);
            $task = $this->syncInspectionTask($actorUserId, $air);
            $this->notifyInspectorsOfSubmittedAir($actorUserId, $air, $task);
            $after = $this->snapshotAuditFields($air);

            $this->auditLogs->record(
                action: 'gso.air.submitted',
                subject: $air,
                changesOld: $before,
                changesNew: $after,
                meta: array_filter([
                    'actor_user_id' => $actorUserId,
                    'task_id' => $task?->id ? (string) $task->id : null,
                ]),
                message: 'GSO AIR submitted: ' . $this->airLabel($air),
                display: $this->buildLifecycleDisplay(
                    summary: 'AIR submitted for next workflow steps',
                    air: $air,
                    beforeStatus: AirStatuses::label((string) ($before['status'] ?? '')),
                    afterStatus: AirStatuses::label((string) ($after['status'] ?? '')),
                ),
            );

            return $this->airs->findOrFail((string) $air->id, true);
        });
    }

    public function createFollowUpFromInspection(string $actorUserId, string $airId): Air
    {
        return DB::transaction(function () use ($actorUserId, $airId) {
            $source = $this->airs->findOrFail($airId, true);

            if ($source->trashed()) {
                throw ValidationException::withMessages([
                    'air' => ['Restore this AIR before creating a follow-up draft.'],
                ]);
            }

            if ((string) ($source->status ?? '') !== AirStatuses::INSPECTED) {
                throw ValidationException::withMessages([
                    'status' => ['Only inspected AIR records can create a follow-up AIR.'],
                ]);
            }

            $existingFollowUp = Air::query()
                ->where('parent_air_id', (string) $source->id)
                ->whereNull('deleted_at')
                ->orderByDesc('continuation_no')
                ->orderByDesc('created_at')
                ->first();

            if ($existingFollowUp) {
                return $this->airs->findOrFail((string) $existingFollowUp->id, true);
            }

            $pendingItems = AirItem::query()
                ->where('air_id', (string) $source->id)
                ->orderBy('item_name_snapshot')
                ->orderBy('created_at')
                ->get()
                ->filter(fn (AirItem $item): bool => $this->itemNeedsFollowUp($item))
                ->values();

            if ($pendingItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => ['This AIR has no unresolved items to carry into a follow-up AIR.'],
                ]);
            }

            $continuationNo = max(2, ((int) ($source->continuation_no ?? 1)) + 1);
            $actor = User::query()->find($actorUserId);

            $followUp = $this->airs->create([
                'parent_air_id' => (string) $source->id,
                'continuation_no' => $continuationNo,
                'po_number' => (string) ($source->po_number ?? ''),
                'po_date' => $source->po_date?->toDateString(),
                'air_number' => null,
                'air_date' => now()->toDateString(),
                'invoice_number' => null,
                'invoice_date' => null,
                'supplier_name' => (string) ($source->supplier_name ?? ''),
                'requesting_department_id' => (string) ($source->requesting_department_id ?? ''),
                'requesting_department_name_snapshot' => (string) ($source->requesting_department_name_snapshot ?? ''),
                'requesting_department_code_snapshot' => (string) ($source->requesting_department_code_snapshot ?? ''),
                'fund_source_id' => (string) ($source->fund_source_id ?? ''),
                'fund' => (string) ($source->fund ?? ''),
                'status' => AirStatuses::DRAFT,
                'date_received' => null,
                'received_completeness' => null,
                'received_notes' => null,
                'date_inspected' => null,
                'inspection_verified' => null,
                'inspection_notes' => null,
                'inspected_by_name' => (string) ($source->inspected_by_name ?? ''),
                'accepted_by_name' => (string) ($source->accepted_by_name ?? ''),
                'created_by_user_id' => $actorUserId,
                'created_by_name_snapshot' => $this->resolveActorLabel($actor),
                'remarks' => null,
            ]);

            foreach ($pendingItems as $sourceItem) {
                AirItem::query()->create([
                    'air_id' => (string) $followUp->id,
                    'item_id' => (string) $sourceItem->item_id,
                    'stock_no_snapshot' => $sourceItem->stock_no_snapshot,
                    'item_name_snapshot' => $sourceItem->item_name_snapshot,
                    'description_snapshot' => $sourceItem->description_snapshot,
                    'unit_snapshot' => $sourceItem->unit_snapshot,
                    'acquisition_cost' => $sourceItem->acquisition_cost,
                    'qty_ordered' => (int) ($sourceItem->qty_ordered ?? 0),
                    'qty_delivered' => 0,
                    'qty_accepted' => 0,
                    'tracking_type_snapshot' => (string) ($sourceItem->tracking_type_snapshot ?? ''),
                    'requires_serial_snapshot' => (bool) ($sourceItem->requires_serial_snapshot ?? false),
                    'is_semi_expendable_snapshot' => (bool) ($sourceItem->is_semi_expendable_snapshot ?? false),
                    'remarks' => null,
                ]);
            }

            $followUp = $this->submitDraft($actorUserId, (string) $followUp->id);
            $this->notifyRolesOfCreatedFollowUpAir($actorUserId, $source, $followUp, null);

            $this->auditLogs->record(
                action: 'gso.air.follow-up.created',
                subject: $followUp,
                changesOld: [],
                changesNew: [
                    'parent_air_id' => (string) $source->id,
                    'continuation_no' => $continuationNo,
                    'po_number' => (string) ($followUp->po_number ?? ''),
                    'copied_items' => $pendingItems->count(),
                    'status' => (string) ($followUp->status ?? ''),
                ],
                meta: array_filter([
                    'actor_user_id' => $actorUserId,
                    'source_air_id' => (string) $source->id,
                ]),
                message: 'GSO follow-up AIR created from inspection: ' . $this->airLabel($followUp),
                display: [
                    'summary' => 'Follow-up AIR created from partial inspection',
                    'subject_label' => $this->airLabel($followUp),
                    'sections' => [[
                        'title' => 'Follow-up AIR',
                        'items' => [
                            ['label' => 'Source AIR', 'before' => 'None', 'after' => $this->airLabel($source)],
                            ['label' => 'Follow-up No.', 'before' => 'None', 'after' => 'Follow-up #' . $continuationNo],
                            ['label' => 'Copied Unresolved Items', 'before' => '0', 'after' => (string) $pendingItems->count()],
                            ['label' => 'Workflow Status', 'before' => 'None', 'after' => AirStatuses::label((string) ($followUp->status ?? ''))],
                        ],
                    ]],
                ],
            );

            return $this->airs->findOrFail((string) $followUp->id, true);
        });
    }

    public function reopenInspection(string $actorUserId, string $airId, ?string $reason = null): Air
    {
        return DB::transaction(function () use ($actorUserId, $airId, $reason) {
            $air = $this->airs->findOrFail($airId, true);

            if ($air->trashed()) {
                throw ValidationException::withMessages([
                    'air' => ['Restore this AIR before reopening its inspection workspace.'],
                ]);
            }

            if ((string) ($air->status ?? '') !== AirStatuses::INSPECTED) {
                throw ValidationException::withMessages([
                    'status' => ['Only inspected AIR records can be reopened for inspection.'],
                ]);
            }

            $before = $this->snapshotAuditFields($air);
            $air->status = AirStatuses::SUBMITTED;
            $air = $this->airs->save($air);
            $after = $this->snapshotAuditFields($air);

            $task = $this->tasks->findLatestBySubject('air', (string) $air->id);
            if ($task) {
                $this->tasks->syncTaskContext(
                    taskId: (string) $task->id,
                    data: [
                        'eligible_roles' => ['Administrator', 'admin', 'Staff', 'Inspector'],
                        'air_id' => (string) $air->id,
                        'air_number' => (string) ($air->air_number ?? ''),
                        'po_number' => (string) ($air->po_number ?? ''),
                        'requesting_department_id' => (string) ($air->requesting_department_id ?? ''),
                        'requesting_department_name_snapshot' => (string) ($air->requesting_department_name_snapshot ?? ''),
                        'fund' => $this->resolveFundLabel($air),
                        'subject_url' => $this->inspectionUrl($air),
                    ],
                    assignmentMode: 'clear',
                    assignedToUserId: null,
                    title: $this->buildSubmittedTaskTitle($air),
                    description: $this->buildSubmittedTaskDescription($air),
                    type: 'air_inspection',
                    mergeData: false,
                );

                $reopenNote = $reason !== null && trim($reason) !== ''
                    ? 'AIR inspection reopened. Reason: ' . trim($reason)
                    : 'AIR inspection reopened.';

                if ((string) ($task->status ?? '') !== Task::STATUS_PENDING) {
                    $this->tasks->changeStatus(
                        actorUserId: $actorUserId,
                        taskId: (string) $task->id,
                        toStatus: Task::STATUS_PENDING,
                        note: $reopenNote,
                    );
                } else {
                    $this->tasks->recordEvent(
                        actorUserId: $actorUserId,
                        taskId: (string) $task->id,
                        eventType: 'gso_air_inspection_reopened',
                        note: $reopenNote,
                        meta: [
                            'air_id' => (string) $air->id,
                            'status_after' => (string) ($air->status ?? ''),
                        ],
                    );
                }
            }

            $message = $reason !== null && trim($reason) !== ''
                ? 'GSO AIR inspection reopened: ' . $this->airLabel($air) . '. Reason: ' . trim($reason)
                : 'GSO AIR inspection reopened: ' . $this->airLabel($air);

            $this->auditLogs->record(
                action: 'gso.air.inspection.reopened',
                subject: $air,
                changesOld: ['status' => $before['status'] ?? null],
                changesNew: ['status' => $after['status'] ?? null],
                meta: array_filter([
                    'actor_user_id' => $actorUserId,
                    'reason' => $reason !== null ? trim($reason) : null,
                ], fn (mixed $value): bool => $value !== null && $value !== ''),
                message: $message,
                display: [
                    'summary' => 'AIR inspection reopened',
                    'subject_label' => $this->airLabel($air),
                    'sections' => [[
                        'title' => 'Inspection Reopened',
                        'items' => array_values(array_filter([
                            ['label' => 'Workflow Status', 'before' => AirStatuses::label((string) ($before['status'] ?? '')), 'after' => AirStatuses::label((string) ($after['status'] ?? ''))],
                            $reason !== null && trim($reason) !== '' ? ['label' => 'Reason', 'before' => 'None', 'after' => trim($reason)] : null,
                        ])),
                    ]],
                ],
            );

            return $air;
        });
    }

    public function delete(string $actorUserId, string $airId): void
    {
        DB::transaction(function () use ($actorUserId, $airId) {
            $air = $this->airs->findOrFail($airId, true);

            if ($air->trashed()) {
                return;
            }

            $before = $this->snapshotAuditFields($air);
            $this->airs->delete($air);

            $this->auditLogs->record(
                action: 'gso.air.deleted',
                subject: $air,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO AIR archived: ' . $this->airLabel($air),
                display: $this->buildLifecycleDisplay(
                    summary: 'AIR archived',
                    air: $air,
                    beforeStatus: 'Active Record',
                    afterStatus: 'Archived',
                ),
            );
        });
    }

    public function restore(string $actorUserId, string $airId): void
    {
        DB::transaction(function () use ($actorUserId, $airId) {
            $air = $this->airs->findOrFail($airId, true);

            if (! $air->trashed()) {
                return;
            }

            $beforeDeletedAt = $air->deleted_at?->toDateTimeString();
            $this->airs->restore($air);
            $air = $this->airs->findOrFail($airId, true);

            $this->auditLogs->record(
                action: 'gso.air.restored',
                subject: $air,
                changesOld: ['deleted_at' => $beforeDeletedAt],
                changesNew: ['restored_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO AIR restored: ' . $this->airLabel($air),
                display: $this->buildLifecycleDisplay(
                    summary: 'AIR restored',
                    air: $air,
                    beforeStatus: 'Archived',
                    afterStatus: 'Active Record',
                ),
            );
        });
    }

    public function forceDelete(string $actorUserId, string $airId): void
    {
        DB::transaction(function () use ($actorUserId, $airId) {
            $air = $this->airs->findOrFail($airId, true);
            $before = $this->snapshotAuditFields($air);

            $this->auditLogs->record(
                action: 'gso.air.force_deleted',
                subject: $air,
                changesOld: $before,
                changesNew: ['force_deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO AIR permanently deleted: ' . $this->airLabel($air),
                display: $this->buildLifecycleDisplay(
                    summary: 'AIR permanently deleted',
                    air: $air,
                    beforeStatus: $air->trashed() ? 'Archived' : 'Active Record',
                    afterStatus: 'Deleted',
                ),
            );

            $this->airs->forceDelete($air);
        });
    }

    private function ensureSignatoryRecords(string $actorUserId, Air $air): void
    {
        $departmentId = $this->nullableString($air->requesting_department_id);
        $office = $this->nullableString($air->requesting_department_name_snapshot);

        foreach ([
            $this->nullableString($air->inspected_by_name),
            $this->nullableString($air->accepted_by_name),
        ] as $signatoryName) {
            if ($signatoryName === null || $this->isPlaceholderSignatoryName($signatoryName)) {
                continue;
            }

            $this->accountablePersons->createOrResolve($actorUserId, [
                'full_name' => $signatoryName,
                'department_id' => $departmentId,
                'office' => $office,
                'is_active' => true,
            ]);
        }
    }

    private function isPlaceholderSignatoryName(string $value): bool
    {
        $normalized = strtoupper(trim($value));

        return in_array($normalized, ['TBD', 'TO BE DETERMINED', 'N/A', '-'], true);
    }

    private function reconcileLegacyFundSource(Air $air): Air
    {
        if ($this->nullableString($air->fund_source_id) !== null) {
            return $air;
        }

        $legacyFundValue = $this->nullableString($air->fund);

        if ($legacyFundValue === null) {
            return $air;
        }

        $matched = FundSource::query()
            ->withTrashed()
            ->where(function (Builder $query) use ($legacyFundValue) {
                $query->where('name', $legacyFundValue)
                    ->orWhere('code', $legacyFundValue);
            })
            ->orderByRaw('CASE WHEN deleted_at IS NULL AND is_active = 1 THEN 0 ELSE 1 END')
            ->orderBy('code')
            ->orderBy('name')
            ->first();

        if (! $matched) {
            return $air;
        }

        $air->fund_source_id = (string) $matched->id;
        $air->fund = $this->nullableString($matched->name) ?? $legacyFundValue;

        return $this->airs->save($air);
    }

    private function syncInspectionTask(string $actorUserId, Air $air): ?Task
    {
        $taskData = [
            'eligible_roles' => ['Administrator', 'Staff', 'Inspector'],
            'air_id' => (string) $air->id,
            'air_number' => (string) ($air->air_number ?? ''),
            'po_number' => (string) ($air->po_number ?? ''),
            'requesting_department_id' => (string) ($air->requesting_department_id ?? ''),
            'requesting_department_name_snapshot' => (string) ($air->requesting_department_name_snapshot ?? ''),
            'fund' => $this->resolveFundLabel($air),
            'subject_url' => $this->inspectionUrl($air),
        ];

        $title = $this->buildSubmittedTaskTitle($air);
        $description = $this->buildSubmittedTaskDescription($air);
        $task = $this->tasks->findLatestBySubject('air', (string) $air->id);

        if (! $task) {
            $task = $this->tasks->createUnassigned(
                actorUserId: $actorUserId,
                title: $title,
                description: $description,
                type: 'air_inspection',
                subjectType: 'air',
                subjectId: (string) $air->id,
                data: $taskData,
            );
        } else {
            $task = $this->tasks->syncTaskContext(
                taskId: (string) $task->id,
                data: $taskData,
                assignmentMode: 'clear',
                assignedToUserId: null,
                title: $title,
                description: $description,
                type: 'air_inspection',
                mergeData: false,
            );
        }

        if ((string) $task->status !== Task::STATUS_PENDING) {
            $task = $this->tasks->changeStatus(
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                toStatus: Task::STATUS_PENDING,
                note: 'AIR draft submitted.',
            );
        } else {
            $this->tasks->recordEvent(
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                eventType: 'workflow_submitted',
                note: 'AIR draft submitted.',
            );
        }

        return $task;
    }

    private function buildSubmittedTaskTitle(Air $air): string
    {
        $airNumber = trim((string) ($air->air_number ?? ''));
        $poNumber = trim((string) ($air->po_number ?? ''));

        if ($poNumber !== '') {
            return "Inspect Purchase Order {$poNumber}";
        }

        if ($airNumber !== '') {
            return "Inspect AIR {$airNumber}";
        }

        return 'Inspect AIR Delivery';
    }

    private function buildSubmittedTaskDescription(Air $air): string
    {
        $details = [];

        $airNumber = trim((string) ($air->air_number ?? ''));
        if ($airNumber !== '') {
            $details[] = "AIR No.: {$airNumber}.";
        }

        $department = trim((string) ($air->requesting_department_name_snapshot ?? ''));
        if ($department !== '') {
            $details[] = "Requesting office: {$department}.";
        }

        $supplier = trim((string) ($air->supplier_name ?? ''));
        if ($supplier !== '') {
            $details[] = "Supplier: {$supplier}.";
        }

        $details[] = 'Review the delivery and complete the inspection details, delivered and accepted quantities, unit records, and photos.';

        return implode(' ', $details);
    }

    private function notifyInspectorsOfSubmittedAir(string $actorUserId, Air $air, ?Task $task): void
    {
        $roleNames = $this->workflowNotifications->rolesForEvent('GSO', 'air.submitted');

        if ($roleNames === []) {
            return;
        }

        $airNumber = trim((string) ($air->air_number ?? ''));
        $poNumber = trim((string) ($air->po_number ?? ''));
        $titleSuffix = $airNumber !== '' ? $airNumber : ($poNumber !== '' ? $poNumber : $this->airLabel($air));

        $taskUrl = $task?->id ? $this->gsoTaskShowUrl((string) $task->id) : $this->inspectionUrl($air);
        $message = $this->renderWorkflowNotificationMessage(
            $this->workflowNotifications->messageTemplateForEvent('GSO', 'air.submitted'),
            [
                'air_label' => $this->airLabel($air),
                'air_number' => $airNumber,
                'po_number' => $poNumber,
                'task_url' => $taskUrl,
                'inspection_url' => $this->inspectionUrl($air),
                'actor_name' => $this->actorDisplayName($actorUserId),
            ],
            'A submitted AIR is ready for inspection review.'
        );

        $this->notifications->notifyUsersByRoles(
            roleNames: $roleNames,
            actorUserId: $actorUserId,
            type: 'gso.air.submitted',
            title: 'AIR ready for inspection: ' . $titleSuffix,
            message: $message,
            entityType: 'air',
            entityId: (string) $air->id,
            data: array_filter([
                'air_id' => (string) $air->id,
                'air_number' => $airNumber !== '' ? $airNumber : null,
                'po_number' => $poNumber !== '' ? $poNumber : null,
                'task_id' => $task?->id ? (string) $task->id : null,
                'url' => $taskUrl,
                'subject_url' => $this->inspectionUrl($air),
            ], static fn (mixed $value): bool => $value !== null && $value !== ''),
        );
    }

    private function notifyRolesOfCreatedFollowUpAir(string $actorUserId, Air $sourceAir, Air $followUpAir, ?Task $task): void
    {
        $roleNames = $this->workflowNotifications->rolesForEvent('GSO', 'air.follow_up_created');

        if ($roleNames === []) {
            return;
        }

        $airNumber = trim((string) ($followUpAir->air_number ?? ''));
        $poNumber = trim((string) ($followUpAir->po_number ?? ''));
        $titleSuffix = $airNumber !== '' ? $airNumber : ($poNumber !== '' ? $poNumber : $this->airLabel($followUpAir));
        $followUpUrl = in_array((string) ($followUpAir->status ?? ''), [AirStatuses::SUBMITTED, AirStatuses::IN_PROGRESS, AirStatuses::INSPECTED], true)
            ? $this->inspectionUrl($followUpAir)
            : $this->editUrl($followUpAir);
        $taskUrl = $task?->id ? $this->gsoTaskShowUrl((string) $task->id) : $followUpUrl;
        $message = $this->renderWorkflowNotificationMessage(
            $this->workflowNotifications->messageTemplateForEvent('GSO', 'air.follow_up_created'),
            [
                'air_label' => $this->airLabel($followUpAir),
                'air_number' => $airNumber,
                'po_number' => $poNumber,
                'source_air_label' => $this->airLabel($sourceAir),
                'task_url' => $taskUrl,
                'follow_up_url' => $followUpUrl,
                'actor_name' => $this->actorDisplayName($actorUserId),
            ],
            'A follow-up AIR draft is created for unresolved inspection items. Click to open the assigned task and continue the workflow.'
        );

        $this->notifications->notifyUsersByRoles(
            roleNames: $roleNames,
            actorUserId: $actorUserId,
            type: 'gso.air.follow-up.created',
            title: 'Follow-up AIR created: ' . $titleSuffix,
            message: $message,
            entityType: 'air',
            entityId: (string) $followUpAir->id,
            data: array_filter([
                'air_id' => (string) $followUpAir->id,
                'parent_air_id' => (string) $sourceAir->id,
                'air_number' => $airNumber !== '' ? $airNumber : null,
                'po_number' => $poNumber !== '' ? $poNumber : null,
                'task_id' => $task?->id ? (string) $task->id : null,
                'url' => $taskUrl,
                'subject_url' => $followUpUrl,
            ], static fn (mixed $value): bool => $value !== null && $value !== ''),
        );
    }

    /**
     * @param  array<string, string|null>  $variables
     */
    private function renderWorkflowNotificationMessage(string $template, array $variables, string $fallback): string
    {
        $template = trim($template);

        if ($template === '') {
            return $fallback;
        }

        $replacements = [];
        foreach ($variables as $key => $value) {
            $replacements['{' . trim((string) $key) . '}'] = trim((string) ($value ?? ''));
        }

        $rendered = strtr($template, $replacements);
        $rendered = preg_replace('/\s+/', ' ', trim($rendered)) ?? '';

        return $rendered !== '' ? $rendered : $fallback;
    }

    private function actorDisplayName(string $actorUserId): string
    {
        $actor = User::query()->find($actorUserId);

        return $this->resolveActorLabel($actor);
    }

    private function gsoTaskShowUrl(string $taskId): string
    {
        try {
            return route('gso.tasks.show', ['id' => $taskId]);
        } catch (\Throwable) {
            return '/gso/tasks/' . $taskId;
        }
    }

    private function resolveFundLabel(Air $air): ?string
    {
        $relatedName = trim((string) ($air->fundSource?->name ?? ''));

        if ($relatedName !== '') {
            return $relatedName;
        }

        return $this->nullableString($air->fund);
    }

    private function inspectionUrl(Air $air): string
    {
        try {
            return route('gso.air.inspect', ['air' => $air->id]);
        } catch (\Throwable) {
            return '/gso/air/' . (string) $air->id . '/inspect';
        }
    }

    private function editUrl(Air $air): string
    {
        try {
            return route('gso.air.edit', ['air' => $air->id]);
        } catch (\Throwable) {
            return '/gso/air/' . (string) $air->id . '/edit';
        }
    }

    private function resolveDefaultDepartment(?User $actor): ?Department
    {
        $actorDepartmentId = trim((string) ($actor?->primary_department_id ?? ''));

        if ($actorDepartmentId !== '') {
            $department = Department::query()
                ->where('id', $actorDepartmentId)
                ->whereNull('deleted_at')
                ->first();

            if ($department) {
                return $department;
            }
        }

        return Department::query()
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('code')
            ->orderBy('name')
            ->first();
    }

    private function resolveDefaultFundSource(): ?FundSource
    {
        $query = FundSource::query()
            ->whereNull('deleted_at')
            ->where('is_active', true);

        $preferred = (clone $query)
            ->where(function (Builder $subQuery) {
                $subQuery->where('code', 'like', '%GF%')
                    ->orWhere('name', 'like', '%General Fund%');
            })
            ->orderBy('code')
            ->orderBy('name')
            ->first();

        if ($preferred) {
            return $preferred;
        }

        return $query
            ->orderBy('code')
            ->orderBy('name')
            ->first();
    }

    private function resolveActorLabel(?User $actor): string
    {
        $username = trim((string) ($actor?->username ?? ''));
        $email = trim((string) ($actor?->email ?? ''));

        if ($username !== '' && $email !== '') {
            return "{$username} ({$email})";
        }

        return $username !== '' ? $username : ($email !== '' ? $email : 'System User');
    }

    private function generatePlaceholderPoNumber(): string
    {
        $prefix = now()->format('Ymd');
        $sequence = Air::query()
            ->where('po_number', 'like', "PO-DRAFT-{$prefix}-%")
            ->withTrashed()
            ->count() + 1;

        return sprintf('PO-DRAFT-%s-%04d', $prefix, $sequence);
    }

    private function generateAirNumber(): string
    {
        $year = now()->format('Y');
        $sequence = Air::query()
            ->withTrashed()
            ->whereNotNull('air_number')
            ->where('air_number', 'like', "AIR-{$year}-%")
            ->count() + 1;

        return sprintf('AIR-%s-%04d', $year, $sequence);
    }

    private function resolveDepartmentForUpdate(array $data, Air $air): ?Department
    {
        $departmentId = trim((string) ($data['requesting_department_id'] ?? $air->requesting_department_id ?? ''));

        if ($departmentId === '') {
            return null;
        }

        return Department::query()->findOrFail($departmentId);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizedPayload(array $data, Air $air, ?Department $department): array
    {
        return [
            'po_number' => $this->cleanString($data['po_number'] ?? $air->po_number ?? ''),
            'po_date' => $data['po_date'] ?? $air->po_date?->toDateString(),
            'air_number' => $this->nullableString($data['air_number'] ?? $air->air_number),
            'air_date' => $data['air_date'] ?? $air->air_date?->toDateString(),
            'invoice_number' => $this->nullableString($data['invoice_number'] ?? $air->invoice_number),
            'invoice_date' => $this->nullableString($data['invoice_date'] ?? $air->invoice_date?->toDateString()),
            'supplier_name' => $this->cleanString($data['supplier_name'] ?? $air->supplier_name ?? ''),
            'requesting_department_id' => $department?->id,
            'requesting_department_name_snapshot' => $department?->name,
            'requesting_department_code_snapshot' => $department?->code,
            'fund_source_id' => $this->nullableString($data['fund_source_id'] ?? $air->fund_source_id),
            'fund' => $this->resolveFundSourceName($data['fund_source_id'] ?? $air->fund_source_id, $air->fund),
            'inspected_by_name' => $this->cleanString($data['inspected_by_name'] ?? $air->inspected_by_name ?? ''),
            'accepted_by_name' => $this->cleanString($data['accepted_by_name'] ?? $air->accepted_by_name ?? ''),
            'remarks' => $this->nullableString($data['remarks'] ?? $air->remarks),
        ];
    }

    private function resolveFundSourceName(mixed $fundSourceId, mixed $fallback): ?string
    {
        $fundSourceId = trim((string) ($fundSourceId ?? ''));

        if ($fundSourceId === '') {
            return $this->nullableString($fallback);
        }

        $fundSource = FundSource::query()->withTrashed()->find($fundSourceId);

        return $fundSource?->name
            ? trim((string) $fundSource->name)
            : $this->nullableString($fallback);
    }

    private function assertEditableDraft(Air $air): void
    {
        if ($air->trashed()) {
            throw ValidationException::withMessages([
                'air' => ['Restore this AIR before editing it.'],
            ]);
        }

        if ((string) ($air->status ?? '') !== AirStatuses::DRAFT) {
            throw ValidationException::withMessages([
                'status' => ['Only draft AIR records can be edited in this migration slice.'],
            ]);
        }
    }

    private function assertHeaderComplete(Air $air): void
    {
        $requiredFields = [
            'po_number' => 'PO Number',
            'po_date' => 'PO Date',
            'air_date' => 'AIR Date',
            'supplier_name' => 'Supplier Name',
            'requesting_department_id' => 'Requesting Department',
            'fund_source_id' => 'Fund Source',
            'inspected_by_name' => 'Inspected By',
            'accepted_by_name' => 'Accepted By',
        ];

        $errors = [];

        foreach ($requiredFields as $field => $label) {
            if (blank($air->{$field})) {
                $errors[$field] = [$label . ' is required before submitting this AIR.'];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function itemNeedsFollowUp(AirItem $item): bool
    {
        return (int) ($item->qty_accepted ?? 0) < (int) ($item->qty_ordered ?? 0);
    }

    private function assertDraftHasItems(Air $air): void
    {
        $hasItems = AirItem::query()
            ->where('air_id', $air->id)
            ->exists();

        if ($hasItems) {
            return;
        }

        throw ValidationException::withMessages([
            'items' => ['Add at least one item before submitting this AIR.'],
        ]);
    }

    private function assertDraftItemUnitsValid(Air $air): void
    {
        $items = AirItem::query()
            ->where('air_id', $air->id)
            ->with([
                'item' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'base_unit']),
                'item.unitConversions' => fn ($query) => $query
                    ->select(['id', 'item_id', 'from_unit', 'multiplier']),
            ])
            ->orderBy('item_name_snapshot')
            ->get();

        $errors = [];

        foreach ($items as $airItem) {
            $item = $airItem->item;
            $label = trim((string) ($airItem->item_name_snapshot ?? 'Item'));

            if (! $item) {
                $errors["items.{$airItem->id}.item"] = ["{$label}: linked item record was not found."];
                continue;
            }

            $options = $item->getAvailableUnitOptions();
            if ($options === []) {
                $errors["items.{$airItem->id}.unit_snapshot"] = [
                    "{$label}: this item has no configured units. Update the item setup first.",
                ];
                continue;
            }

            $unitSnapshot = trim((string) ($airItem->unit_snapshot ?? ''));
            if ($unitSnapshot === '') {
                $errors["items.{$airItem->id}.unit_snapshot"] = [
                    "{$label}: unit is required before submitting this AIR.",
                ];
                continue;
            }

            if ($item->canonicalUnitValue($unitSnapshot) !== null) {
                continue;
            }

            $allowed = implode(', ', array_map(
                static fn (array $option): string => (string) ($option['value'] ?? ''),
                $options
            ));

            $errors["items.{$airItem->id}.unit_snapshot"] = [
                "{$label}: saved unit \"{$unitSnapshot}\" is no longer valid. Choose one of: {$allowed}.",
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function assertPoNumberAvailable(string $poNumber, string $ignoreAirId, bool $rootOnly): void
    {
        $poNumber = $this->cleanString($poNumber);

        if ($poNumber === '') {
            return;
        }

        $exists = Air::query()
            ->withTrashed()
            ->where('po_number', $poNumber)
            ->where('id', '!=', $ignoreAirId)
            ->when($rootOnly, fn (Builder $query) => $query->whereNull('parent_air_id'))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'po_number' => ['PO Number already exists for another AIR record.'],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshotAuditFields(Air $air): array
    {
        return [
            'po_number' => $this->nullableString($air->po_number),
            'po_date' => $air->po_date?->toDateString(),
            'air_number' => $this->nullableString($air->air_number),
            'air_date' => $air->air_date?->toDateString(),
            'invoice_number' => $this->nullableString($air->invoice_number),
            'invoice_date' => $air->invoice_date?->toDateString(),
            'supplier_name' => $this->nullableString($air->supplier_name),
            'requesting_department_id' => $this->nullableString($air->requesting_department_id),
            'requesting_department_name_snapshot' => $this->nullableString($air->requesting_department_name_snapshot),
            'requesting_department_code_snapshot' => $this->nullableString($air->requesting_department_code_snapshot),
            'fund_source_id' => $this->nullableString($air->fund_source_id),
            'status' => $this->nullableString($air->status),
            'inspected_by_name' => $this->nullableString($air->inspected_by_name),
            'accepted_by_name' => $this->nullableString($air->accepted_by_name),
            'remarks' => $this->nullableString($air->remarks),
        ];
    }

    private function buildUpdatedDisplay(array $before, array $after): array
    {
        return [
            'summary' => 'AIR draft updated',
            'sections' => [[
                'title' => 'Header Changes',
                'items' => [
                    ['label' => 'PO Number', 'before' => $this->displayValue($before['po_number'] ?? null), 'after' => $this->displayValue($after['po_number'] ?? null)],
                    ['label' => 'PO Date', 'before' => $this->displayValue($before['po_date'] ?? null), 'after' => $this->displayValue($after['po_date'] ?? null)],
                    ['label' => 'AIR Number', 'before' => $this->displayValue($before['air_number'] ?? null), 'after' => $this->displayValue($after['air_number'] ?? null)],
                    ['label' => 'AIR Date', 'before' => $this->displayValue($before['air_date'] ?? null), 'after' => $this->displayValue($after['air_date'] ?? null)],
                    ['label' => 'Invoice Number', 'before' => $this->displayValue($before['invoice_number'] ?? null), 'after' => $this->displayValue($after['invoice_number'] ?? null)],
                    ['label' => 'Invoice Date', 'before' => $this->displayValue($before['invoice_date'] ?? null), 'after' => $this->displayValue($after['invoice_date'] ?? null)],
                    ['label' => 'Supplier', 'before' => $this->displayValue($before['supplier_name'] ?? null), 'after' => $this->displayValue($after['supplier_name'] ?? null)],
                    ['label' => 'Department', 'before' => $this->displayValue($before['requesting_department_name_snapshot'] ?? null), 'after' => $this->displayValue($after['requesting_department_name_snapshot'] ?? null)],
                    ['label' => 'Fund Source ID', 'before' => $this->displayValue($before['fund_source_id'] ?? null), 'after' => $this->displayValue($after['fund_source_id'] ?? null)],
                    ['label' => 'Inspected By', 'before' => $this->displayValue($before['inspected_by_name'] ?? null), 'after' => $this->displayValue($after['inspected_by_name'] ?? null)],
                    ['label' => 'Accepted By', 'before' => $this->displayValue($before['accepted_by_name'] ?? null), 'after' => $this->displayValue($after['accepted_by_name'] ?? null)],
                    ['label' => 'Remarks', 'before' => $this->displayValue($before['remarks'] ?? null), 'after' => $this->displayValue($after['remarks'] ?? null)],
                ],
            ]],
        ];
    }

    private function buildLifecycleDisplay(string $summary, Air $air, string $beforeStatus, string $afterStatus): array
    {
        return [
            'summary' => $summary . ': ' . $this->airLabel($air),
            'subject_label' => $this->airLabel($air),
            'sections' => [[
                'title' => 'Workflow State',
                'items' => [
                    ['label' => 'Status', 'before' => $beforeStatus, 'after' => $afterStatus],
                    ['label' => 'PO Number', 'before' => null, 'after' => $this->displayValue($air->po_number)],
                    ['label' => 'AIR Number', 'before' => null, 'after' => $this->displayValue($air->air_number)],
                ],
            ]],
        ];
    }

    private function airLabel(Air $air): string
    {
        $poNumber = trim((string) ($air->po_number ?? ''));
        $airNumber = trim((string) ($air->air_number ?? ''));

        if ($poNumber !== '' && $airNumber !== '') {
            return "{$poNumber} / {$airNumber}";
        }

        return $poNumber !== '' ? $poNumber : ($airNumber !== '' ? $airNumber : 'AIR Record');
    }

    private function displayValue(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : 'None';
    }

    private function cleanString(mixed $value): string
    {
        return preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ?? '';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = $this->cleanString($value);

        return $value !== '' ? $value : null;
    }
}
