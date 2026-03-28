<?php

namespace App\Modules\GSO\Services;

use App\Core\Models\Module;
use App\Core\Models\Notification;
use App\Core\Models\User;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Repositories\Tasks\Contracts\TaskRepositoryInterface;
use App\Core\Support\CurrentContext;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\Ics;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\Itr;
use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Models\Ptr;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Models\Stock;
use App\Modules\GSO\Models\Wmr;
use App\Modules\GSO\Services\Contracts\GsoDashboardServiceInterface;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\ParStatuses;

class GsoDashboardService implements GsoDashboardServiceInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
        private readonly UserRepositoryInterface $users,
        private readonly CurrentContext $context,
    ) {
    }

    public function build(User $user): array
    {
        $user->loadMissing('profile');

        $moduleId = $this->resolveModuleId();
        $taskCounts = $this->taskCounts($user, $moduleId);
        $workflowCards = $this->workflowCards();

        return [
            'greetingName' => $user->profile?->full_name ?: $user->username ?: 'User',
            'taskCounts' => $taskCounts,
            'unreadNotifications' => $this->unreadNotifications((string) $user->id, $moduleId),
            'workflowCards' => $workflowCards,
            'documentsAwaitingAction' => array_sum(array_column($workflowCards, 'action_count')),
            'openDraftDocuments' => array_sum(array_column($workflowCards, 'draft_count')),
            'recentDocuments' => $this->recentDocuments(),
            'recentNotifications' => $this->recentNotifications((string) $user->id, $moduleId),
            'inventorySnapshot' => $this->inventorySnapshot(),
            'quickLinks' => $this->quickLinks(),
        ];
    }

    private function taskCounts(User $user, string $moduleId): array
    {
        if ($moduleId === '') {
            return [
                'my' => 0,
                'claimable' => 0,
            ];
        }

        return $this->tasks->countsForSidebar(
            (string) $user->id,
            [
                $moduleId => $this->users->getRoleNamesInModule($user, $moduleId),
            ],
            [$moduleId]
        );
    }

    private function workflowCards(): array
    {
        return [
            [
                'module' => 'AIR',
                'description' => 'Acceptance, inspection, and supplier delivery follow-up.',
                'href' => route('gso.air.index'),
                'draft_count' => Air::query()->where('status', 'draft')->count(),
                'action_count' => Air::query()->whereIn('status', ['submitted', 'in_progress'])->count(),
                'action_label' => 'Needs inspection',
                'done_count' => Air::query()->where('status', 'inspected')->count(),
                'done_label' => 'Inspected',
            ],
            [
                'module' => 'RIS',
                'description' => 'Consumable issue requests and stock release workflow.',
                'href' => route('gso.ris.index'),
                'draft_count' => Ris::query()->where('status', 'draft')->count(),
                'action_count' => Ris::query()->whereIn('status', ['submitted', 'rejected'])->count(),
                'action_label' => 'Needs attention',
                'done_count' => Ris::query()->where('status', 'issued')->count(),
                'done_label' => 'Issued',
            ],
            [
                'module' => 'PAR',
                'description' => 'Property issuance for PPE currently in the GSO pool.',
                'href' => route('gso.pars.index'),
                'draft_count' => Par::query()->where('status', ParStatuses::DRAFT)->count(),
                'action_count' => Par::query()->where('status', ParStatuses::SUBMITTED)->count(),
                'action_label' => 'Awaiting finalize',
                'done_count' => Par::query()->where('status', ParStatuses::FINALIZED)->count(),
                'done_label' => 'Finalized',
            ],
            [
                'module' => 'ICS',
                'description' => 'Semi-expendable accountability and issuance slips.',
                'href' => route('gso.ics.index'),
                'draft_count' => Ics::query()->where('status', 'draft')->count(),
                'action_count' => Ics::query()->where('status', 'submitted')->count(),
                'action_label' => 'Awaiting finalize',
                'done_count' => Ics::query()->where('status', 'finalized')->count(),
                'done_label' => 'Finalized',
            ],
            [
                'module' => 'PTR',
                'description' => 'Transfers for issued property and accountability changes.',
                'href' => route('gso.ptrs.index'),
                'draft_count' => Ptr::query()->where('status', 'draft')->count(),
                'action_count' => Ptr::query()->where('status', 'submitted')->count(),
                'action_label' => 'Awaiting finalize',
                'done_count' => Ptr::query()->where('status', 'finalized')->count(),
                'done_label' => 'Finalized',
            ],
            [
                'module' => 'ITR',
                'description' => 'Inventory transfer coordination across offices and custodians.',
                'href' => route('gso.itrs.index'),
                'draft_count' => Itr::query()->where('status', 'draft')->count(),
                'action_count' => Itr::query()->where('status', 'submitted')->count(),
                'action_label' => 'Awaiting finalize',
                'done_count' => Itr::query()->where('status', 'finalized')->count(),
                'done_label' => 'Finalized',
            ],
            [
                'module' => 'WMR',
                'description' => 'Waste material reporting, approval, and disposal finalization.',
                'href' => route('gso.wmrs.index'),
                'draft_count' => Wmr::query()->where('status', 'draft')->count(),
                'action_count' => Wmr::query()->whereIn('status', ['submitted', 'approved'])->count(),
                'action_label' => 'Needs action',
                'done_count' => Wmr::query()->where('status', 'finalized')->count(),
                'done_label' => 'Finalized',
            ],
        ];
    }

    private function recentDocuments(): array
    {
        $documents = array_merge(
            Air::query()
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (Air $air) => [
                    'module' => 'AIR',
                    'reference' => $air->air_number ?: ($air->po_number ? 'PO ' . $air->po_number : 'AIR Draft'),
                    'subtext' => (string) ($air->supplier_name ?: $air->requesting_department_name_snapshot ?: 'Acceptance Inspection Report'),
                    'status' => (string) ($air->status ?? '-'),
                    'updated_at' => $air->updated_at,
                    'updated_at_text' => $air->updated_at?->diffForHumans() ?? '-',
                    'url' => in_array((string) $air->status, ['submitted', 'in_progress', 'inspected'], true)
                        ? route('gso.air.inspect', ['air' => (string) $air->id])
                        : route('gso.air.edit', ['air' => (string) $air->id]),
                ])
                ->all(),
            Ris::query()
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (Ris $ris) => [
                    'module' => 'RIS',
                    'reference' => $ris->ris_number ?: 'RIS Draft',
                    'subtext' => (string) ($ris->requesting_department_name_snapshot ?: $ris->purpose ?: 'Requisition and Issue Slip'),
                    'status' => (string) ($ris->status ?? '-'),
                    'updated_at' => $ris->updated_at,
                    'updated_at_text' => $ris->updated_at?->diffForHumans() ?? '-',
                    'url' => route('gso.ris.edit', ['ris' => (string) $ris->id]),
                ])
                ->all(),
            Par::query()
                ->with('department')
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (Par $par) => [
                    'module' => 'PAR',
                    'reference' => $par->par_number ?: 'PAR Draft',
                    'subtext' => (string) ($par->person_accountable ?: $par->department?->name ?: 'Property Acknowledgment Receipt'),
                    'status' => (string) ($par->status ?? '-'),
                    'updated_at' => $par->updated_at,
                    'updated_at_text' => $par->updated_at?->diffForHumans() ?? '-',
                    'url' => route('gso.pars.show', ['par' => (string) $par->id]),
                ])
                ->all(),
            Ics::query()
                ->with('department')
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (Ics $ics) => [
                    'module' => 'ICS',
                    'reference' => $ics->ics_number ?: 'ICS Draft',
                    'subtext' => (string) ($ics->received_by_name ?: $ics->department?->name ?: 'Inventory Custodian Slip'),
                    'status' => (string) ($ics->status ?? '-'),
                    'updated_at' => $ics->updated_at,
                    'updated_at_text' => $ics->updated_at?->diffForHumans() ?? '-',
                    'url' => route('gso.ics.edit', ['ics' => (string) $ics->id]),
                ])
                ->all(),
            Ptr::query()
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (Ptr $ptr) => [
                    'module' => 'PTR',
                    'reference' => $ptr->ptr_number ?: 'PTR Draft',
                    'subtext' => (string) (($ptr->from_accountable_officer ?: 'Unassigned') . ' -> ' . ($ptr->to_accountable_officer ?: 'Unassigned')),
                    'status' => (string) ($ptr->status ?? '-'),
                    'updated_at' => $ptr->updated_at,
                    'updated_at_text' => $ptr->updated_at?->diffForHumans() ?? '-',
                    'url' => route('gso.ptrs.edit', ['ptr' => (string) $ptr->id]),
                ])
                ->all(),
            Itr::query()
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (Itr $itr) => [
                    'module' => 'ITR',
                    'reference' => $itr->itr_number ?: 'ITR Draft',
                    'subtext' => (string) (($itr->from_accountable_officer ?: 'Unassigned') . ' -> ' . ($itr->to_accountable_officer ?: 'Unassigned')),
                    'status' => (string) ($itr->status ?? '-'),
                    'updated_at' => $itr->updated_at,
                    'updated_at_text' => $itr->updated_at?->diffForHumans() ?? '-',
                    'url' => route('gso.itrs.edit', ['itr' => (string) $itr->id]),
                ])
                ->all(),
            Wmr::query()
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (Wmr $wmr) => [
                    'module' => 'WMR',
                    'reference' => $wmr->wmr_number ?: 'WMR Draft',
                    'subtext' => (string) ($wmr->place_of_storage ?: $wmr->remarks ?: 'Waste Materials Report'),
                    'status' => (string) ($wmr->status ?? '-'),
                    'updated_at' => $wmr->updated_at,
                    'updated_at_text' => $wmr->updated_at?->diffForHumans() ?? '-',
                    'url' => route('gso.wmrs.edit', ['wmr' => (string) $wmr->id]),
                ])
                ->all(),
        );

        usort($documents, function (array $a, array $b): int {
            $aTime = $a['updated_at']?->getTimestamp() ?? 0;
            $bTime = $b['updated_at']?->getTimestamp() ?? 0;

            return $bTime <=> $aTime;
        });

        return array_slice($documents, 0, 12);
    }

    private function recentNotifications(string $userId, string $moduleId): array
    {
        return Notification::query()
            ->where('notifiable_user_id', $userId)
            ->when($moduleId !== '', fn ($query) => $query->where('module_id', $moduleId))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Notification $notification) => [
                'title' => (string) ($notification->title ?? 'Notification'),
                'message' => (string) ($notification->message ?? ''),
                'created_at_text' => $notification->created_at?->diffForHumans() ?? '-',
                'is_read' => $notification->read_at !== null,
            ])
            ->all();
    }

    private function unreadNotifications(string $userId, string $moduleId): int
    {
        return Notification::query()
            ->where('notifiable_user_id', $userId)
            ->whereNull('read_at')
            ->when($moduleId !== '', fn ($query) => $query->where('module_id', $moduleId))
            ->count();
    }

    private function inventorySnapshot(): array
    {
        return [
            [
                'label' => 'Property in GSO Pool',
                'value' => InventoryItem::query()
                    ->where('custody_state', InventoryCustodyStates::POOL)
                    ->where(function ($query) {
                        $query->where('is_ics', false)->orWhereNull('is_ics');
                    })
                    ->count(),
                'helper' => 'PPE items ready for PAR, PTR, or other downstream actions.',
                'href' => route('gso.inventory-items.index'),
            ],
            [
                'label' => 'Issued PPE Items',
                'value' => InventoryItem::query()
                    ->where('custody_state', InventoryCustodyStates::ISSUED)
                    ->where(function ($query) {
                        $query->where('is_ics', false)->orWhereNull('is_ics');
                    })
                    ->count(),
                'helper' => 'Property already acknowledged and no longer in the pool.',
                'href' => route('gso.inventory-items.index'),
            ],
            [
                'label' => 'Issued ICS Items',
                'value' => InventoryItem::query()
                    ->where('custody_state', InventoryCustodyStates::ISSUED)
                    ->where('is_ics', true)
                    ->count(),
                'helper' => 'Semi-expendable items currently under accountability.',
                'href' => route('gso.inventory-items.index'),
            ],
            [
                'label' => 'Low Stock Lines',
                'value' => Stock::query()->where('on_hand', '<=', 10)->count(),
                'helper' => 'Stock records at or below the current alert threshold.',
                'href' => route('gso.stocks.index'),
            ],
        ];
    }

    private function quickLinks(): array
    {
        return [
            ['label' => 'My Tasks', 'icon' => 'ri-task-line', 'href' => route('gso.tasks.index', ['scope' => 'mine', 'archived' => 'active'])],
            ['label' => 'Notifications', 'icon' => 'ri-notification-3-line', 'href' => route('notifications.index')],
            ['label' => 'AIR Workspace', 'icon' => 'ri-file-list-3-line', 'href' => route('gso.air.index')],
            ['label' => 'RIS Workspace', 'icon' => 'ri-inbox-archive-line', 'href' => route('gso.ris.index')],
            ['label' => 'PAR Workspace', 'icon' => 'ri-archive-drawer-line', 'href' => route('gso.pars.index')],
            ['label' => 'ICS Workspace', 'icon' => 'ri-file-paper-2-line', 'href' => route('gso.ics.index')],
            ['label' => 'PTR Workspace', 'icon' => 'ri-arrow-left-right-line', 'href' => route('gso.ptrs.index')],
            ['label' => 'ITR Workspace', 'icon' => 'ri-arrow-go-forward-line', 'href' => route('gso.itrs.index')],
            ['label' => 'WMR Workspace', 'icon' => 'ri-delete-bin-6-line', 'href' => route('gso.wmrs.index')],
            ['label' => 'Inventory Items', 'icon' => 'ri-box-3-line', 'href' => route('gso.inventory-items.index')],
        ];
    }

    private function resolveModuleId(): string
    {
        $moduleId = trim((string) ($this->context->moduleId() ?? ''));

        if ($moduleId !== '') {
            return $moduleId;
        }

        return (string) (Module::query()
            ->where('code', 'GSO')
            ->value('id') ?? '');
    }
}
