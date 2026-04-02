<?php

namespace App\Modules\GSO\Services\Air;

use App\Core\Models\User;
use App\Core\Services\Tasks\Contracts\TaskServiceInterface;
use App\Core\Support\AdminContextAuthorizer;

class AirInspectionWorkspaceAccessService
{
    public function __construct(
        private readonly AdminContextAuthorizer $authorizer,
        private readonly TaskServiceInterface $tasks,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function resolve(?User $actor, string $airId): array
    {
        $airId = trim($airId);

        if (! $actor || $airId === '') {
            return [
                'can_manage' => false,
                'has_inspect_permission' => false,
                'is_assigned_inspector' => false,
                'is_assignment_elevated' => false,
                'assigned_to_user_id' => null,
                'warning' => null,
            ];
        }

        $hasInspectPermission = $this->authorizer->allowsPermission($actor, 'air.inspect');
        $isAssignmentElevated = $this->authorizer->allowsAnyPermission($actor, [
            'air.update',
            'air.manage_items',
            'air.manage_files',
            'air.finalize_inspection',
            'air.reopen_inspection',
            'air.promote_inventory',
        ]);

        $task = $this->tasks->findLatestBySubject('air', $airId);
        $assignedToUserId = trim((string) ($task?->assigned_to_user_id ?? ''));
        $isAssignedInspector = $hasInspectPermission
            && $assignedToUserId !== ''
            && $assignedToUserId === (string) $actor->id;

        $canManage = $isAssignmentElevated || $isAssignedInspector;
        $warning = null;

        if (! $canManage && $hasInspectPermission) {
            $warning = 'You are not currently assigned to this AIR inspection. The workspace is read-only until the task is assigned to you.';
        }

        return [
            'can_manage' => $canManage,
            'has_inspect_permission' => $hasInspectPermission,
            'is_assigned_inspector' => $isAssignedInspector,
            'is_assignment_elevated' => $isAssignmentElevated,
            'assigned_to_user_id' => $assignedToUserId !== '' ? $assignedToUserId : null,
            'warning' => $warning,
        ];
    }

    public function canManage(?User $actor, string $airId): bool
    {
        return (bool) ($this->resolve($actor, $airId)['can_manage'] ?? false);
    }
}
