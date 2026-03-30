<?php

namespace App\Core\Services\Access;

use App\Core\Builders\Contracts\Access\UserIdentityChangeRequestAuditDisplayBuilderInterface;
use App\Core\Models\Tasks\Task;
use App\Core\Models\User;
use App\Core\Models\UserIdentityChangeRequest;
use App\Core\Repositories\Tasks\Contracts\TaskRepositoryInterface;
use App\Core\Repositories\Contracts\UserIdentityChangeRequestRepositoryInterface;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Services\Contracts\Access\UserIdentityChangeRequestServiceInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\Notifications\NotificationServiceInterface;
use App\Core\Services\Tasks\Contracts\TaskServiceInterface;
use App\Core\Support\CurrentContext;
use App\Core\Support\ProfileRouteResolver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class UserIdentityChangeRequestService implements UserIdentityChangeRequestServiceInterface
{
    private const REVIEWER_ROLE_NAMES = ['Administrator', 'admin'];

    public function __construct(
        private readonly UserIdentityChangeRequestRepositoryInterface $requests,
        private readonly UserRepositoryInterface $users,
        private readonly TaskServiceInterface $tasks,
        private readonly TaskRepositoryInterface $taskRecords,
        private readonly NotificationServiceInterface $notifications,
        private readonly AuditLogServiceInterface $audit,
        private readonly CurrentContext $context,
        private readonly ProfileRouteResolver $profileRoutes,
        private readonly UserIdentityChangeRequestAuditDisplayBuilderInterface $auditDisplayBuilder,
    ) {}

    public function latestForUser(User $user): ?UserIdentityChangeRequest
    {
        return $this->requests->findLatestForUser((string) $user->id);
    }

    public function submitFromProfileUpdate(User $user, array $identityData, ?string $reason = null): array
    {
        $requested = $this->normalizeIdentityPayload($identityData);
        $current = $this->currentIdentityPayload($user);

        if (! $this->identityPayloadChanged($current, $requested)) {
            return [
                'status' => 'unchanged',
                'request' => null,
                'pending_request' => $this->requests->findPendingForUser((string) $user->id),
            ];
        }

        return DB::transaction(function () use ($user, $current, $requested, $reason) {
            $pending = $this->requests->findPendingForUser((string) $user->id);

            if ($pending) {
                $this->ensureAdministratorReviewCoverage($user, $pending);

                return [
                    'status' => 'blocked',
                    'request' => null,
                    'pending_request' => $pending,
                ];
            }

            $request = $this->requests->create([
                'user_id' => (string) $user->id,
                'current_first_name' => $current['first_name'],
                'current_last_name' => $current['last_name'],
                'current_middle_name' => $current['middle_name'],
                'current_name_extension' => $current['name_extension'],
                'requested_first_name' => $requested['first_name'],
                'requested_last_name' => $requested['last_name'],
                'requested_middle_name' => $requested['middle_name'],
                'requested_name_extension' => $requested['name_extension'],
                'reason' => $this->normalizeNullableText($reason),
                'status' => UserIdentityChangeRequest::STATUS_PENDING,
            ]);

            $this->createAdministratorReviewTasks($user, $request);
            $this->notifyAdministratorReviewers($user, $request);

            $this->audit->record(
                action: 'user.identity_change_requested',
                subject: $request,
                changesOld: $this->currentAuditSnapshot($request),
                changesNew: $this->requestedAuditSnapshot($request),
                display: $this->auditDisplayBuilder->buildRequestedDisplay($user->fresh(['profile']), $request)
            );

            return [
                'status' => 'created',
                'request' => $request,
                'pending_request' => $request,
            ];
        });
    }

    public function indexData(User $reviewer, array $filters = [], int $perPage = 15): array
    {
        $reviewModuleId = $this->reviewContextModuleId();
        $this->ensureReviewerCanManageModule($reviewer, $reviewModuleId);

        $filters = [
            'status' => trim((string) ($filters['status'] ?? 'pending')),
            'search' => trim((string) ($filters['search'] ?? '')),
            'module_ids' => [$reviewModuleId],
        ];

        return [
            'filters' => $filters,
            'requests' => $this->requests->paginateForAdmin($filters, $perPage),
            'statusOptions' => [
                'pending' => 'Pending Approval',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'all' => 'All Requests',
            ],
        ];
    }

    public function showData(User $reviewer, string $requestId): array
    {
        $request = $this->findRequestOrFail($requestId);
        $reviewModuleId = $this->reviewContextModuleId();

        $this->ensureReviewerCanManageRequest($reviewer, $request, $reviewModuleId);

        return [
            'identityRequest' => $request,
            'comparisonRows' => $this->comparisonRows($request),
            'relatedTask' => $this->taskRecords->findLatestBySubject(
                UserIdentityChangeRequest::class,
                (string) $request->id,
                $reviewModuleId
            ),
        ];
    }

    public function approve(string $requestId, User $reviewer, ?string $reviewNotes = null): UserIdentityChangeRequest
    {
        return DB::transaction(function () use ($requestId, $reviewer, $reviewNotes) {
            $request = $this->findRequestOrFail($requestId);
            $this->ensureReviewerCanManageRequest($reviewer, $request);
            $this->ensurePending($request);

            $user = $request->user()->with('profile')->firstOrFail();

            $user->profile()->updateOrCreate([], [
                'first_name' => $request->requested_first_name,
                'middle_name' => $request->requested_middle_name,
                'last_name' => $request->requested_last_name,
                'name_extension' => $request->requested_name_extension,
            ]);

            $request->status = UserIdentityChangeRequest::STATUS_APPROVED;
            $request->reviewed_by = (string) $reviewer->id;
            $request->reviewed_at = now();
            $request->review_notes = $this->normalizeNullableText($reviewNotes);

            $request = $this->requests->save($request);

            $this->completeRelatedTask($request, (string) $reviewer->id, 'Identity change request approved.');
            $this->notifyRequesterReviewed($request, $reviewer, approved: true);

            $this->audit->record(
                action: 'user.identity_change_approved',
                subject: $request,
                changesOld: array_merge(
                    ['status' => UserIdentityChangeRequest::STATUS_PENDING],
                    $this->currentAuditSnapshot($request)
                ),
                changesNew: array_merge(
                    [
                        'status' => UserIdentityChangeRequest::STATUS_APPROVED,
                        'reviewed_by' => (string) $reviewer->id,
                        'reviewed_at' => optional($request->reviewed_at)->toDateTimeString(),
                        'review_notes' => $request->review_notes,
                    ],
                    $this->requestedAuditSnapshot($request)
                ),
                display: $this->auditDisplayBuilder->buildApprovedDisplay(
                    $user->fresh(['profile']),
                    $request,
                    $reviewer->fresh(['profile'])
                )
            );

            return $request;
        });
    }

    public function reject(string $requestId, User $reviewer, ?string $reviewNotes = null): UserIdentityChangeRequest
    {
        return DB::transaction(function () use ($requestId, $reviewer, $reviewNotes) {
            $request = $this->findRequestOrFail($requestId);
            $this->ensureReviewerCanManageRequest($reviewer, $request);
            $this->ensurePending($request);

            $user = $request->user()->with('profile')->firstOrFail();

            $request->status = UserIdentityChangeRequest::STATUS_REJECTED;
            $request->reviewed_by = (string) $reviewer->id;
            $request->reviewed_at = now();
            $request->review_notes = $this->normalizeNullableText($reviewNotes);

            $request = $this->requests->save($request);

            $this->completeRelatedTask($request, (string) $reviewer->id, 'Identity change request rejected.');
            $this->notifyRequesterReviewed($request, $reviewer, approved: false);

            $this->audit->record(
                action: 'user.identity_change_rejected',
                subject: $request,
                changesOld: array_merge(
                    ['status' => UserIdentityChangeRequest::STATUS_PENDING],
                    $this->currentAuditSnapshot($request)
                ),
                changesNew: [
                    'status' => UserIdentityChangeRequest::STATUS_REJECTED,
                    'reviewed_by' => (string) $reviewer->id,
                    'reviewed_at' => optional($request->reviewed_at)->toDateTimeString(),
                    'review_notes' => $request->review_notes,
                ],
                display: $this->auditDisplayBuilder->buildRejectedDisplay(
                    $user->fresh(['profile']),
                    $request,
                    $reviewer->fresh(['profile'])
                )
            );

            return $request;
        });
    }

    private function createAdministratorReviewTaskLegacy(User $user, UserIdentityChangeRequest $request): void
    {
        $reviewerIds = $this->resolveActiveReviewerIds((string) $user->id);

        $this->tasks->createUnassigned(
            actorUserId: (string) $user->id,
            title: 'Review identity change: ' . $this->userDisplayName($user),
            description: 'Administrator review required for requested updates to the user\'s official identity fields.',
            type: 'identity_change_review',
            subjectType: UserIdentityChangeRequest::class,
            subjectId: (string) $request->id,
            data: [
                'subject_url' => $this->identityRequestUrl($request),
                'requesting_user_id' => (string) $user->id,
                'requesting_user_name' => $this->userDisplayName($user),
                'requesting_username' => $user->username,
                'requesting_email' => $user->email,
                'submitted_at' => optional($request->created_at)->toDateTimeString(),
                'reason' => $request->reason,
                'current_full_name' => $request->currentFullName(),
                'requested_full_name' => $request->requestedFullName(),
                'current_values' => $this->currentAuditSnapshot($request),
                'requested_values' => $this->requestedAuditSnapshot($request),
                'eligible_roles' => self::REVIEWER_ROLE_NAMES,
                'reviewer_user_ids' => $reviewerIds,
            ]
        );
    }

    private function notifyAdministratorReviewersLegacy(User $user, UserIdentityChangeRequest $request): void
    {
        $reviewerIds = $this->resolveActiveReviewerIds((string) $user->id);

        if ($reviewerIds === []) {
            return;
        }

        $this->notifications->notifyUsers(
            recipientUserIds: $reviewerIds,
            actorUserId: (string) $user->id,
            type: 'user.identity_change_requested',
            title: 'Identity change request needs review',
            message: $this->userDisplayName($user) . ' submitted a profile identity change request for administrator review.',
            entityType: 'user_identity_change_requests',
            entityId: (string) $request->id,
            data: [
                'url' => $this->identityRequestUrl($request),
                'requesting_user_name' => $this->userDisplayName($user),
                'current_full_name' => $request->currentFullName(),
                'requested_full_name' => $request->requestedFullName(),
                'submitted_at' => optional($request->created_at)->toDateTimeString(),
                'reason' => $request->reason,
            ],
            moduleId: $this->requireModuleId(),
        );
    }

    private function createAdminTask(User $user, UserIdentityChangeRequest $request): void
    {
        $reviewerIds = $this->resolveActiveReviewerIds((string) $user->id);
        $title = 'Review identity change: ' . $this->userDisplayName($user);
        $description = 'Review requested updates to the user’s official identity fields.';
        $data = [
            'subject_url' => $this->identityRequestUrl($request),
            'requesting_user_id' => (string) $user->id,
            'requesting_user_name' => $this->userDisplayName($user),
            'requesting_username' => $user->username,
            'requesting_email' => $user->email,
            'submitted_at' => optional($request->created_at)->toDateTimeString(),
            'reason' => $request->reason,
            'current_values' => $this->currentAuditSnapshot($request),
            'requested_values' => $this->requestedAuditSnapshot($request),
        ];

        if ($reviewerIds !== []) {
            $this->tasks->createAndAssign(
                actorUserId: (string) $user->id,
                assigneeUserId: $reviewerIds[0],
                title: $title,
                description: $description,
                type: 'identity_change_review',
                subjectType: UserIdentityChangeRequest::class,
                subjectId: (string) $request->id,
                data: $data
            );

            return;
        }

        $this->tasks->createUnassigned(
            actorUserId: (string) $user->id,
            title: $title,
            description: $description,
            type: 'identity_change_review',
            subjectType: UserIdentityChangeRequest::class,
            subjectId: (string) $request->id,
            data: $data
        );
    }

    private function notifyAdministrators(User $user, UserIdentityChangeRequest $request): void
    {
        $reviewerIds = $this->resolveActiveReviewerIds((string) $user->id);

        if ($reviewerIds === []) {
            return;
        }

        $this->notifications->notifyUsers(
            recipientUserIds: $reviewerIds,
            actorUserId: (string) $user->id,
            type: 'user.identity_change_requested',
            title: 'Identity change request submitted',
            message: $this->userDisplayName($user) . ' submitted a profile identity change request for review.',
            entityType: 'user_identity_change_requests',
            entityId: (string) $request->id,
            data: [
                'url' => $this->identityRequestUrl($request),
                'requesting_user_name' => $this->userDisplayName($user),
                'submitted_at' => optional($request->created_at)->toDateTimeString(),
            ],
            moduleId: $this->requireModuleId(),
        );
    }

    private function createAdministratorReviewTasks(User $user, UserIdentityChangeRequest $request): void
    {
        foreach ($this->reviewTargetModuleIdsForUser($user) as $moduleId) {
            $this->createAdministratorReviewTaskForModule($user, $request, $moduleId);
        }
    }

    private function notifyAdministratorReviewers(User $user, UserIdentityChangeRequest $request): void
    {
        foreach ($this->reviewTargetModuleIdsForUser($user) as $moduleId) {
            $this->notifyAdministratorReviewersForModule($user, $request, $moduleId);
        }
    }

    private function ensureAdministratorReviewCoverage(User $user, UserIdentityChangeRequest $request): void
    {
        foreach ($this->reviewTargetModuleIdsForUser($user) as $moduleId) {
            $existingTask = $this->taskRecords->findLatestBySubject(
                UserIdentityChangeRequest::class,
                (string) $request->id,
                $moduleId
            );

            if ($existingTask) {
                continue;
            }

            $this->createAdministratorReviewTaskForModule($user, $request, $moduleId);
            $this->notifyAdministratorReviewersForModule($user, $request, $moduleId);
        }
    }

    private function createAdministratorReviewTaskForModule(
        User $user,
        UserIdentityChangeRequest $request,
        string $moduleId
    ): void {
        $reviewerIds = $this->resolveActiveReviewerIdsForModule($moduleId, (string) $user->id);

        $this->tasks->createUnassignedInModule(
            ownerModuleId: $moduleId,
            actorUserId: (string) $user->id,
            title: 'Review identity change: ' . $this->userDisplayName($user),
            description: 'Administrator review required for requested updates to the user\'s official identity fields.',
            type: 'identity_change_review',
            subjectType: UserIdentityChangeRequest::class,
            subjectId: (string) $request->id,
            data: [
                'subject_url' => $this->identityRequestUrl($request),
                'requesting_user_id' => (string) $user->id,
                'requesting_user_name' => $this->userDisplayName($user),
                'requesting_username' => $user->username,
                'requesting_email' => $user->email,
                'submitted_at' => optional($request->created_at)->toDateTimeString(),
                'reason' => $request->reason,
                'current_full_name' => $request->currentFullName(),
                'requested_full_name' => $request->requestedFullName(),
                'current_values' => $this->currentAuditSnapshot($request),
                'requested_values' => $this->requestedAuditSnapshot($request),
                'eligible_roles' => self::REVIEWER_ROLE_NAMES,
                'reviewer_user_ids' => $reviewerIds,
                'review_module_id' => $moduleId,
            ]
        );
    }

    private function notifyAdministratorReviewersForModule(
        User $user,
        UserIdentityChangeRequest $request,
        string $moduleId
    ): void {
        $reviewerIds = $this->resolveActiveReviewerIdsForModule($moduleId, (string) $user->id);

        if ($reviewerIds === []) {
            return;
        }

        $this->notifications->notifyUsers(
            recipientUserIds: $reviewerIds,
            actorUserId: (string) $user->id,
            type: 'user.identity_change_requested',
            title: 'Identity change request needs review',
            message: $this->userDisplayName($user) . ' submitted a profile identity change request for administrator review.',
            entityType: 'user_identity_change_requests',
            entityId: (string) $request->id,
            data: [
                'url' => $this->identityRequestUrl($request),
                'requesting_user_name' => $this->userDisplayName($user),
                'current_full_name' => $request->currentFullName(),
                'requested_full_name' => $request->requestedFullName(),
                'submitted_at' => optional($request->created_at)->toDateTimeString(),
                'reason' => $request->reason,
                'review_module_id' => $moduleId,
            ],
            moduleId: $moduleId,
        );
    }

    private function notifyRequesterReviewed(UserIdentityChangeRequest $request, User $reviewer, bool $approved): void
    {
        $this->notifications->notifyUser(
            notifiableUserId: (string) $request->user_id,
            actorUserId: (string) $reviewer->id,
            type: $approved ? 'user.identity_change_approved' : 'user.identity_change_rejected',
            title: $approved ? 'Identity change approved' : 'Identity change rejected',
            message: $approved
                ? 'Your identity change request has been approved and your official profile has been updated.'
                : 'Your identity change request was reviewed and rejected.',
            entityType: 'user_identity_change_requests',
            entityId: (string) $request->id,
            data: [
                'url' => $this->profileUrl(),
                'review_notes' => $request->review_notes,
                'reviewed_at' => optional($request->reviewed_at)->toDateTimeString(),
            ],
            moduleId: $this->requireModuleId(),
        );
    }

    private function completeRelatedTask(UserIdentityChangeRequest $request, string $actorUserId, string $note): void
    {
        foreach ($this->reviewTargetModuleIdsForRequest($request) as $moduleId) {
            $task = $this->taskRecords->findLatestBySubject(
                UserIdentityChangeRequest::class,
                (string) $request->id,
                $moduleId
            );

            if (! $task || in_array((string) $task->status, [Task::STATUS_DONE, Task::STATUS_CANCELLED], true)) {
                continue;
            }

            $this->tasks->changeStatus($actorUserId, (string) $task->id, Task::STATUS_DONE, $note);
        }
    }

    private function resolveActiveReviewerIds(string $excludeUserId = ''): array
    {
        $moduleId = $this->requireModuleId();

        $roleIds = $this->users->getUserIdsByRolesInModule(self::REVIEWER_ROLE_NAMES, $moduleId);
        $activeUserIds = $this->users->getActiveUsersForModule($moduleId)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->values()
            ->all();

        $reviewerIds = array_values(array_intersect($roleIds, $activeUserIds));

        if ($excludeUserId !== '') {
            $reviewerIds = array_values(array_diff($reviewerIds, [$excludeUserId]));
        }

        return $reviewerIds;
    }

    private function resolveActiveReviewerIdsForModule(string $moduleId, string $excludeUserId = ''): array
    {
        $roleIds = $this->users->getUserIdsByRolesInModule(self::REVIEWER_ROLE_NAMES, $moduleId);
        $activeUserIds = $this->users->getActiveUsersForModule($moduleId)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->values()
            ->all();

        $reviewerIds = array_values(array_intersect($roleIds, $activeUserIds));

        if ($excludeUserId !== '') {
            $reviewerIds = array_values(array_diff($reviewerIds, [$excludeUserId]));
        }

        return $reviewerIds;
    }

    private function reviewContextModuleId(): string
    {
        $moduleId = trim((string) ($this->context->moduleId() ?? ''));

        if ($moduleId !== '') {
            return $moduleId;
        }

        throw new AuthorizationException('Identity change approvals require an active module context.');
    }

    private function ensureReviewerCanManageRequest(
        User $reviewer,
        UserIdentityChangeRequest $request,
        ?string $reviewModuleId = null
    ): void {
        $reviewModuleId = trim((string) ($reviewModuleId ?: $this->reviewContextModuleId()));

        $this->ensureReviewerCanManageModule($reviewer, $reviewModuleId);

        if (! in_array($reviewModuleId, $this->reviewTargetModuleIdsForRequest($request), true)) {
            throw new AuthorizationException('You cannot review this identity change request from the current module context.');
        }
    }

    private function ensureReviewerCanManageModule(User $reviewer, string $moduleId): void
    {
        if (! $this->users->findActiveInModule((string) $reviewer->id, $moduleId)) {
            throw new AuthorizationException('You do not have active access to review requests in this module.');
        }

        $roleNames = $this->users->getRoleNamesInModule($reviewer, $moduleId);
        $normalizedRoles = array_map('mb_strtolower', $roleNames);
        $allowedRoles = array_map('mb_strtolower', self::REVIEWER_ROLE_NAMES);

        if (array_intersect($normalizedRoles, $allowedRoles) === []) {
            throw new AuthorizationException('Only module administrators can review identity change requests in this module.');
        }
    }

    private function reviewTargetModuleIdsForRequest(UserIdentityChangeRequest $request): array
    {
        $user = $request->relationLoaded('user') ? $request->user : $request->user()->first();

        if (! $user instanceof User) {
            return $this->fallbackReviewModuleIds();
        }

        return $this->reviewTargetModuleIdsForUser($user);
    }

    private function reviewTargetModuleIdsForUser(User $user): array
    {
        $moduleIds = $user->userModules()
            ->where('is_active', true)
            ->pluck('module_id')
            ->map(fn ($moduleId) => trim((string) $moduleId))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $moduleIds !== [] ? $moduleIds : $this->fallbackReviewModuleIds();
    }

    private function fallbackReviewModuleIds(): array
    {
        $moduleId = trim((string) ($this->context->moduleId() ?? ''));

        return $moduleId !== '' ? [$moduleId] : [];
    }

    private function findRequestOrFail(string $requestId): UserIdentityChangeRequest
    {
        $request = $this->requests->findById($requestId);

        if ($request) {
            return $request;
        }

        throw (new ModelNotFoundException())->setModel(UserIdentityChangeRequest::class, [$requestId]);
    }

    private function ensurePending(UserIdentityChangeRequest $request): void
    {
        if ($request->isPending()) {
            return;
        }

        throw ValidationException::withMessages([
            'request' => 'Only pending identity change requests can be reviewed.',
        ]);
    }

    private function requireModuleId(): string
    {
        $moduleId = trim((string) $this->context->moduleId());

        if ($moduleId !== '') {
            return $moduleId;
        }

        throw new \RuntimeException('Core platform module context is not available for identity requests.');
    }

    private function currentIdentityPayload(User $user): array
    {
        $profile = $user->profile;

        return [
            'first_name' => $this->normalizeRequiredText($profile?->first_name),
            'middle_name' => $this->normalizeNullableText($profile?->middle_name),
            'last_name' => $this->normalizeRequiredText($profile?->last_name),
            'name_extension' => $this->normalizeNullableText($profile?->name_extension),
        ];
    }

    private function normalizeIdentityPayload(array $identityData): array
    {
        return [
            'first_name' => $this->normalizeRequiredText($identityData['first_name'] ?? null),
            'middle_name' => $this->normalizeNullableText($identityData['middle_name'] ?? null),
            'last_name' => $this->normalizeRequiredText($identityData['last_name'] ?? null),
            'name_extension' => $this->normalizeNullableText($identityData['name_extension'] ?? null),
        ];
    }

    private function identityPayloadChanged(array $current, array $requested): bool
    {
        return $current !== $requested;
    }

    private function currentAuditSnapshot(UserIdentityChangeRequest $request): array
    {
        return [
            'first_name' => $request->current_first_name,
            'middle_name' => $request->current_middle_name,
            'last_name' => $request->current_last_name,
            'name_extension' => $request->current_name_extension,
        ];
    }

    private function requestedAuditSnapshot(UserIdentityChangeRequest $request): array
    {
        return [
            'first_name' => $request->requested_first_name,
            'middle_name' => $request->requested_middle_name,
            'last_name' => $request->requested_last_name,
            'name_extension' => $request->requested_name_extension,
            'reason' => $request->reason,
        ];
    }

    private function comparisonRows(UserIdentityChangeRequest $request): array
    {
        return [
            ['label' => 'First Name', 'current' => $request->current_first_name ?: 'None', 'requested' => $request->requested_first_name ?: 'None'],
            ['label' => 'Middle Name', 'current' => $request->current_middle_name ?: 'None', 'requested' => $request->requested_middle_name ?: 'None'],
            ['label' => 'Last Name', 'current' => $request->current_last_name ?: 'None', 'requested' => $request->requested_last_name ?: 'None'],
            ['label' => 'Name Extension', 'current' => $request->current_name_extension ?: 'None', 'requested' => $request->requested_name_extension ?: 'None'],
        ];
    }

    private function identityRequestUrl(UserIdentityChangeRequest $request): ?string
    {
        return Route::has('identity-change-requests.show')
            ? route('identity-change-requests.show', $request)
            : null;
    }

    private function profileUrl(): ?string
    {
        return Route::has('profile.index')
            ? $this->profileRoutes->indexUrl()
            : null;
    }

    private function normalizeRequiredText(mixed $value): string
    {
        return trim((string) $value);
    }

    private function normalizeNullableText(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function userDisplayName(User $user): string
    {
        $profileName = trim((string) ($user->profile?->full_name ?? ''));

        if ($profileName !== '') {
            return $profileName;
        }

        return (string) ($user->username ?: $user->email ?: 'User');
    }
}
