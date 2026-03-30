@extends('layouts.master')

@section('styles')
<style>
    #task-show-page.task-show-shell {
        width: 100%;
        max-width: none;
        margin: 0;
        padding-inline: 1rem 1.5rem;
    }

    .task-show-layout {
        align-items: start;
        gap: 1.25rem;
    }

    .task-show-summary .box-header,
    .task-show-timeline .box-header {
        padding: 1.15rem 1.35rem 0.95rem;
    }

    .task-show-summary .box-body,
    .task-show-timeline .box-body {
        padding: 1.15rem 1.35rem 1.35rem;
    }

    .task-show-action-row {
        gap: 0.75rem;
    }

    .task-show-action-row .ti-btn {
        border-radius: 0.9rem;
    }

    .task-show-overview-grid {
        gap: 0.9rem;
    }

    .task-show-header-action {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
        border-radius: 9999px;
        padding: 0.7rem 1rem;
        line-height: 1;
        box-shadow: 0 10px 24px rgba(14, 165, 233, 0.16);
    }

    .task-show-header-action i {
        font-size: 1rem;
    }

    .task-show-section-title {
        display: inline-flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .task-show-section-status {
        font-size: 0.875rem;
        font-weight: 600;
        color: #f59e0b;
    }

    .task-show-summary.box,
    .task-show-timeline.box,
    .task-show-stat,
    .task-show-description,
    .task-show-empty {
        border-radius: 1rem;
        background: #ffffff;
    }

    .task-show-summary.box,
    .task-show-timeline.box {
        border: 1px solid rgba(15, 23, 42, 0.06);
        box-shadow: 0 16px 38px rgba(15, 23, 42, 0.04);
    }

    .task-show-summary .box-header,
    .task-show-summary .box-body,
    .task-show-timeline .box-header,
    .task-show-timeline .box-body {
        background: #ffffff;
    }

    .task-show-timeline-list {
        margin-bottom: 0;
        padding-bottom: 0.15rem;
    }

    .task-show-timeline-list::before {
        top: 1.4rem;
        bottom: 0.75rem;
        inset-inline-start: 5.15rem;
        border-style: solid;
        border-color: rgba(124, 58, 237, 0.16);
    }

    .task-show-timeline-list > li {
        min-height: 0;
        padding: 0 0 1rem;
    }

    .task-show-timeline-list .timeline-time {
        width: 4.6rem;
        top: 0.05rem;
    }

    .task-show-timeline-list .timeline-time .date {
        font-size: 0.68rem;
        letter-spacing: 0.08em;
        margin-bottom: 0.18rem;
        text-transform: uppercase;
    }

    .task-show-timeline-list .timeline-time .time {
        font-size: 1rem;
        line-height: 1.1rem;
        font-weight: 600;
    }

    .task-show-timeline-list .timeline-icon {
        inset-inline-start: 4.58rem;
        width: 1.5rem;
        top: 0.45rem;
    }

    .task-show-timeline-list .timeline-icon a {
        width: 0.92rem;
        height: 0.92rem;
        border-width: 0.22rem;
        background: #fff;
        box-shadow: 0 0 0 0.38rem rgba(124, 58, 237, 0.08);
    }

    .task-show-timeline-list .timeline-body {
        margin-left: 6.8rem;
        margin-right: 0;
        top: 0;
        padding: 1rem 1.1rem;
        border: 1px solid rgba(124, 58, 237, 0.08);
        border-radius: 1.1rem;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.04);
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(4px);
    }

    .dark .task-show-summary.box,
    .dark .task-show-timeline.box,
    .dark .task-show-stat,
    .dark .task-show-description,
    .dark .task-show-empty {
        background: rgba(15, 23, 42, 0.9);
    }

    .dark .task-show-summary.box,
    .dark .task-show-timeline.box {
        border-color: rgba(148, 163, 184, 0.12);
        box-shadow: 0 18px 44px rgba(2, 6, 23, 0.28);
    }

    .dark .task-show-summary .box-header,
    .dark .task-show-summary .box-body,
    .dark .task-show-timeline .box-header,
    .dark .task-show-timeline .box-body {
        background: rgba(15, 23, 42, 0.9);
    }

    .dark .task-show-stat,
    .dark .task-show-description,
    .dark .task-show-empty {
        background: rgba(15, 23, 42, 0.64);
        border-color: rgba(148, 163, 184, 0.12);
        box-shadow: none;
    }

    .dark .task-show-header-action {
        box-shadow: 0 10px 24px rgba(14, 165, 233, 0.22);
    }

    .dark .task-show-section-status {
        color: #fbbf24;
    }

    .dark .task-show-timeline-list::before {
        border-color: rgba(167, 139, 250, 0.24);
    }

    .dark .task-show-timeline-list .timeline-time .date,
    .dark .task-show-timeline-list .timeline-time .time {
        color: rgba(226, 232, 240, 0.76);
    }

    .dark .task-show-timeline-list .timeline-icon a {
        background: #1f2937;
        box-shadow: 0 0 0 0.38rem rgba(124, 58, 237, 0.14);
    }

    .dark .task-show-timeline-list .timeline-body {
        background: rgba(30, 41, 59, 0.92);
        border-color: rgba(124, 58, 237, 0.2);
        box-shadow: 0 14px 32px rgba(2, 6, 23, 0.2);
    }

    .dark .task-show-timeline-note {
        background: rgba(76, 29, 149, 0.18);
        border-color: rgba(124, 58, 237, 0.24);
        color: rgba(255, 255, 255, 0.82);
    }

    .dark .task-show-timeline-avatar {
        background: rgba(124, 58, 237, 0.18);
        box-shadow: none;
    }

    .task-show-timeline-list .timeline-main-content {
        gap: 0.95rem;
        align-items: flex-start;
    }

    .task-show-timeline-avatar {
        width: 2.8rem;
        height: 2.8rem;
        min-width: 2.8rem;
        background: rgba(124, 58, 237, 0.12);
        box-shadow: none;
    }

    .task-show-timeline-copy {
        display: grid;
        gap: 0.7rem;
        width: 100%;
    }

    .task-show-timeline-head {
        display: flex;
        gap: 0.85rem;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .task-show-timeline-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .task-show-timeline-note {
        margin-top: 0;
        padding: 0.8rem 0.95rem;
        border-radius: 0.95rem;
        background: rgba(124, 58, 237, 0.045);
        border: 1px solid rgba(124, 58, 237, 0.08);
        color: #4b5563;
    }

    .task-show-timeline-stamp {
        white-space: nowrap;
    }

    @media (max-width: 1279.98px) {
        #task-show-page.task-show-shell {
            padding-inline: 1rem;
        }
    }

    @media (max-width: 991.98px) {
        .task-show-layout {
            gap: 1rem;
        }

        .task-show-timeline-list::before {
            inset-inline-start: 0.8rem;
            top: 1.05rem;
            bottom: 0.55rem;
        }

        .task-show-timeline-list .timeline-time {
            position: relative;
            width: auto;
            top: 0;
            margin-left: 2.15rem;
            margin-bottom: 0.45rem;
            text-align: left !important;
        }

        .task-show-timeline-list .timeline-time .time {
            display: inline-block;
            margin-left: 0.45rem;
        }

        .task-show-timeline-list .timeline-icon {
            inset-inline-start: 0.05rem;
            width: 1.5rem;
            top: 1.3rem;
        }

        .task-show-timeline-list .timeline-body {
            margin-left: 2.3rem;
        }
    }
</style>
@endsection

@section('content')
@php
    $taskRouteNames = array_merge([
        'index' => 'tasks.index',
        'show' => 'tasks.show',
        'claim' => 'tasks.claim',
        'status.update' => 'tasks.status.update',
        'reassign' => 'tasks.reassign',
        'comment.store' => 'tasks.comment.store',
    ], is_array($taskRouteNames ?? null) ? $taskRouteNames : []);
    $tasksShowPageDescription = trim((string) ($tasksShowPageDescription ?? 'Review task details, take action, and follow the complete activity timeline from one page.'))
        ?: 'Review task details, take action, and follow the complete activity timeline from one page.';
    $tasksShowBreadcrumbRootLabel = trim((string) ($tasksShowBreadcrumbRootLabel ?? 'Workflow')) ?: 'Workflow';
    $tasksShowBreadcrumbRootUrl = trim((string) ($tasksShowBreadcrumbRootUrl ?? route($taskRouteNames['index']))) ?: route($taskRouteNames['index']);
    $tasksShowBreadcrumbIndexLabel = trim((string) ($tasksShowBreadcrumbIndexLabel ?? 'Tasks')) ?: 'Tasks';
    $tasksShowBreadcrumbCurrentLabel = trim((string) ($tasksShowBreadcrumbCurrentLabel ?? 'Timeline')) ?: 'Timeline';
    $tasksShowOverviewDescription = trim((string) ($tasksShowOverviewDescription ?? 'Summary, ownership, and available workflow actions stay on the left.'))
        ?: 'Summary, ownership, and available workflow actions stay on the left.';
    $statusLabels = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'done' => 'Done',
        'cancelled' => 'Cancelled',
    ];

    $statusBadgeClasses = [
        'pending' => 'bg-warning/10 text-warning',
        'in_progress' => 'bg-info/10 text-info',
        'done' => 'bg-success/10 text-success',
        'cancelled' => 'bg-danger/10 text-danger',
    ];

    $eventTypeLabels = [
        'created' => 'Task Created',
        'assigned' => 'Task Assigned',
        'claimed' => 'Task Claimed',
        'comment' => 'Comment Added',
        'status_changed' => 'Status Changed',
        'task_reassigned' => 'Task Reassigned',
        'archived' => 'Task Archived',
        'restored' => 'Task Restored',
    ];

    $formatStatusLabel = function (?string $status) use ($statusLabels): string {
        $key = trim((string) $status);

        if ($key === '') {
            return 'N/A';
        }

        return $statusLabels[$key] ?? \Illuminate\Support\Str::headline(str_replace(['-', '_'], ' ', $key));
    };

    $formatEventType = function (?string $eventType) use ($eventTypeLabels): string {
        $key = trim((string) $eventType);

        if ($key === '') {
            return 'Activity';
        }

        return $eventTypeLabels[$key] ?? \Illuminate\Support\Str::headline(str_replace(['-', '_'], ' ', $key));
    };

    $timelineDayLabel = function ($timestamp): string {
        if (! $timestamp) {
            return 'DATE';
        }

        if ($timestamp->isToday()) {
            return 'TODAY';
        }

        if ($timestamp->isYesterday()) {
            return 'YESTERDAY';
        }

        return strtoupper($timestamp->format('l'));
    };

    $actorDisplayName = function ($event): string {
        return trim((string) (
            $event->actor_name_snapshot
            ?? $event->actor_username_snapshot
            ?? $event->actor?->profile?->full_name
            ?? $event->actor?->username
            ?? 'System'
        ));
    };

    $actorInitials = function (?string $name): string {
        $value = trim((string) $name);

        if ($value === '') {
            return 'SY';
        }

        $parts = preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $initials = collect($parts)
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : strtoupper(substr($value, 0, 2));
    };

    $currentStatusKey = trim((string) $task->status);
    $currentStatusLabel = $formatStatusLabel($currentStatusKey);
    $currentStatusClasses = $statusBadgeClasses[$currentStatusKey] ?? 'bg-light text-defaulttextcolor dark:text-white/70';
    $assignedToName = trim((string) ($task->assignedTo?->profile?->full_name ?: ($task->assignedTo?->username ?: 'Unassigned')));
    $createdByName = trim((string) ($task->createdBy?->profile?->full_name ?? $task->createdBy?->username ?? 'System'));
    $typeLabel = $task->type
        ? \Illuminate\Support\Str::headline(str_replace(['-', '_'], ' ', (string) $task->type))
        : 'General';
    $taskViewer = auth()->user();
    $taskAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
    $taskModuleId = (string) $task->module_id;
    $canReassign = $taskAuthorizer->allowsPermissionInModule($taskViewer, 'tasks.reassign', $taskModuleId);
    $canViewAllTaskRecords = $taskAuthorizer->allowsPermissionInModule($taskViewer, 'tasks.view_all', $taskModuleId);
    $normalizedHeaderActions = collect($headerActions ?? [])->map(function ($action) {
        $actionType = ($action['type'] ?? 'link') === 'button' ? 'button' : 'link';
        $actionLabel = trim((string) ($action['label'] ?? ''));
        $actionClasses = trim((string) ($action['classes'] ?? 'ti-btn ti-btn-secondary'));
        $actionHref = (string) ($action['href'] ?? '#');
        $actionAttributes = is_array($action['attributes'] ?? null) ? $action['attributes'] : [];
        $actionButtonType = (string) ($action['button_type'] ?? 'button');
        $attributeHtml = collect($actionAttributes)
            ->map(function ($value, $key) {
                $attrName = is_string($key) ? trim($key) : '';

                if ($attrName === '' || ! preg_match('/^[a-zA-Z0-9_:\-]+$/', $attrName)) {
                    return null;
                }

                return $attrName . '="' . e((string) $value) . '"';
            })
            ->filter()
            ->implode(' ');

        return [
            'type' => $actionType,
            'label' => $actionLabel,
            'classes' => $actionClasses,
            'href' => $actionHref,
            'button_type' => $actionButtonType,
            'attributes_html' => $attributeHtml !== '' ? ' ' . $attributeHtml : '',
        ];
    })->filter(fn ($action) => $action['label'] !== '')->values();
    $normalizedAssignees = collect($assignees ?? [])->map(function ($u) use ($task) {
        $isCurrent = (string) $task->assigned_to_user_id === (string) ($u['id'] ?? '');
        $name = trim((string) ($u['name'] ?? 'Unknown User'));

        return [
            'id' => (string) ($u['id'] ?? ''),
            'label' => $isCurrent ? $name . ' (current)' : $name,
            'disabled' => $isCurrent,
        ];
    })->filter(fn ($u) => $u['id'] !== '')->values();
@endphp

    <div id="task-show-page" class="task-show-shell w-full py-4">
    <div class="block justify-between page-header md:flex">
        <div>
            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">
                {{ $task->title }}
            </h3>
            <p class="text-sm text-[#8c9097] dark:text-white/50 mt-1">
                {{ $tasksShowPageDescription }}
            </p>
        </div>
        <div class="mt-4 md:mt-0">
            <ol class="flex items-center whitespace-nowrap min-w-0">
                <li class="text-[0.813rem] ps-[0.5rem]">
                    <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="{{ $tasksShowBreadcrumbRootUrl }}">
                        {{ $tasksShowBreadcrumbRootLabel }}
                        <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                    </a>
                </li>
                <li class="text-[0.813rem] ps-[0.5rem]">
                    <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="{{ route($taskRouteNames['index']) }}">
                        {{ $tasksShowBreadcrumbIndexLabel }}
                        <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                    </a>
                </li>
                <li class="text-[0.813rem] text-defaulttextcolor font-semibold dark:text-[#8c9097] dark:text-white/50" aria-current="page">
                    {{ $tasksShowBreadcrumbCurrentLabel }}
                </li>
            </ol>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-x-6 task-show-layout">
        <div class="col-span-12 lg:col-span-5 xl:col-span-4">
            <div class="box overflow-hidden task-show-summary">
                <div class="box-header justify-between">
                    <div>
                        <h5 class="box-title task-show-section-title">
                            <span>Task Overview</span>
                            <span class="task-show-section-status">({{ $currentStatusLabel }})</span>
                        </h5>
                        <p class="text-xs text-[#8c9097] dark:text-white/50 mt-1">
                            {{ $tasksShowOverviewDescription }}
                        </p>
                    </div>
                    @if($subjectUrl)
                        <a href="{{ $subjectUrl }}" class="ti-btn ti-btn-secondary-full task-show-header-action">
                            <i class="ti ti-external-link"></i>
                            Open Task
                        </a>
                    @endif
                </div>
                <div class="box-body space-y-5">
                    <div class="flex flex-wrap gap-2 task-show-action-row">
                        @foreach($normalizedHeaderActions as $action)
                            @if($action['type'] === 'link')
                                <a href="{{ $action['href'] }}" class="{{ $action['classes'] }}"{!! $action['attributes_html'] !!}>
                                    {{ $action['label'] }}
                                </a>
                            @else
                                <button type="{{ $action['button_type'] }}" class="{{ $action['classes'] }}"{!! $action['attributes_html'] !!}>
                                    {{ $action['label'] }}
                                </button>
                            @endif
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 task-show-overview-grid">
                        <div class="rounded-lg border border-defaultborder p-4 task-show-stat">
                            <div class="text-xs uppercase tracking-wide text-[#8c9097] dark:text-white/50">Assigned To</div>
                            <div class="mt-1 text-sm font-semibold text-defaulttextcolor dark:text-white">
                                {{ $assignedToName }}
                            </div>
                        </div>
                        <div class="rounded-lg border border-defaultborder p-4 task-show-stat">
                            <div class="text-xs uppercase tracking-wide text-[#8c9097] dark:text-white/50">Created</div>
                            <div class="mt-1 text-sm font-semibold text-defaulttextcolor dark:text-white">
                                {{ $task->created_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                        <div class="rounded-lg border border-defaultborder p-4 task-show-stat">
                            <div class="text-xs uppercase tracking-wide text-[#8c9097] dark:text-white/50">Task Type</div>
                            <div class="mt-1 text-sm font-semibold text-defaulttextcolor dark:text-white">
                                {{ $typeLabel }}
                            </div>
                        </div>
                        <div class="rounded-lg border border-defaultborder p-4 task-show-stat">
                            <div class="text-xs uppercase tracking-wide text-[#8c9097] dark:text-white/50">Created By</div>
                            <div class="mt-1 text-sm font-semibold text-defaulttextcolor dark:text-white">
                                {{ $createdByName }}
                            </div>
                        </div>
                    </div>

                    @if($task->description)
                        <div class="rounded-lg border border-defaultborder p-4 task-show-description">
                            <div class="text-xs uppercase tracking-wide text-[#8c9097] dark:text-white/50 mb-2">Description</div>
                            <div class="text-sm text-defaulttextcolor dark:text-white whitespace-pre-line">
                                {{ $task->description }}
                            </div>
                        </div>
                    @endif

                    @can('claim', $task)
                        <form method="POST" action="{{ route($taskRouteNames['claim'], $task->id) }}">
                            @csrf
                            <input type="hidden" name="redirect_route_name" value="{{ $taskRouteNames['show'] }}">
                            <button type="submit" class="ti-btn ti-btn-primary w-full">Claim Task</button>
                        </form>
                    @endcan

                    @can('updateStatus', $task)
                        <div class="border-t border-defaultborder pt-4">
                            <div class="font-semibold text-defaulttextcolor dark:text-white mb-1">Admin Status Override</div>
                            <p class="text-xs text-[#8c9097] dark:text-white/50 mb-3">
                                Use this only when a workflow needs manual correction. Timeline entries will record the override.
                            </p>

                            <form method="POST"
                                action="{{ route($taskRouteNames['status.update'], $task->id) }}"
                                class="space-y-2 js-task-status-form">
                                @csrf
                                <select name="status" class="ti-form-select w-full">
                                    <option value="pending" @selected($task->status === 'pending')>Pending</option>
                                    <option value="in_progress" @selected($task->status === 'in_progress')>In Progress</option>
                                    <option value="done" @selected($task->status === 'done')>Done</option>
                                    <option value="cancelled" @selected($task->status === 'cancelled')>Cancelled</option>
                                </select>

                                <textarea name="note" rows="3" class="ti-form-input w-full"
                                    placeholder="Optional note for this manual status update"></textarea>

                                <button type="submit" class="ti-btn ti-btn-secondary w-full">Update Status</button>
                            </form>
                        </div>
                    @endcan

                    @if((string) $task->type === 'air_inspection' && ! $canViewAllTaskRecords)
                        <div class="rounded-lg border border-info/20 bg-info/5 p-4">
                            <div class="font-semibold text-defaulttextcolor dark:text-white mb-1">Inspection Workflow</div>
                            <p class="text-sm text-[#8c9097] dark:text-white/50">
                                AIR inspection tasks usually update automatically from the related workflow. Claim the task, complete the inspection, and finalize or reopen it from the linked AIR record.
                            </p>
                        </div>
                    @endif

                    @if($canReassign)
                        <div class="border-t border-defaultborder pt-4">
                            <div class="font-semibold text-defaulttextcolor dark:text-white mb-2">Reassign Task</div>

                            <form method="POST"
                                action="{{ route($taskRouteNames['reassign'], $task->id) }}"
                                class="space-y-2 js-task-reassign-form">
                                @csrf

                                <select name="assignee_user_id" class="ti-form-select w-full" required>
                                    <option value="" disabled selected>Select new assignee</option>

                                    @foreach($normalizedAssignees as $assignee)
                                        <option value="{{ $assignee['id'] }}" {{ $assignee['disabled'] ? 'disabled' : '' }}>
                                            {{ $assignee['label'] }}
                                        </option>
                                    @endforeach
                                </select>

                                <textarea name="note" rows="3" class="ti-form-input w-full"
                                    placeholder="Optional note (reason / instructions)"></textarea>

                                <button type="submit" class="ti-btn ti-btn-warning w-full">Reassign</button>
                            </form>
                        </div>
                    @endif

                    @can('comment', $task)
                        <div class="border-t border-defaultborder pt-4">
                            <div class="font-semibold text-defaulttextcolor dark:text-white mb-2">Add Comment</div>

                            <form method="POST"
                                action="{{ route($taskRouteNames['comment.store'], $task->id) }}"
                                class="space-y-2 js-task-comment-form">
                                @csrf
                                <textarea name="note" rows="3" class="ti-form-input w-full" required
                                    placeholder="Write a comment..."></textarea>
                                <button type="submit" class="ti-btn ti-btn-light w-full">Post Comment</button>
                            </form>
                        </div>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-7 xl:col-span-8">
            <div class="box overflow-hidden h-full task-show-timeline">
                <div class="box-header justify-between">
                    <div>
                        <h5 class="box-title">Timeline</h5>
                        <p class="text-xs text-[#8c9097] dark:text-white/50 mt-1">
                            A tighter event stream with status flow, actor context, and notes in one place.
                        </p>
                    </div>
                    <span class="badge !bg-light text-[#8c9097] dark:text-white/50 whitespace-nowrap">
                        {{ $events->count() }} event(s)
                    </span>
                </div>
                <div class="box-body">
                    @if($events->count() === 0)
                        <div class="rounded-lg border border-dashed border-defaultborder px-4 py-10 text-center task-show-empty">
                            <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">No events yet</div>
                            <p class="text-xs text-[#8c9097] dark:text-white/50 mt-2">
                                Timeline activity will appear here once the task is assigned, claimed, updated, or commented on.
                            </p>
                        </div>
                    @else
                        <div class="overflow-y-auto pe-2" style="max-height: 860px;">
                            <ul class="timeline list-none text-[0.813rem] text-defaulttextcolor dark:text-white/70 mb-0 task-show-timeline-list">
                                @foreach($events as $event)
                                    @php
                                        $eventLabel = $formatEventType($event->event_type);
                                        $actorName = $actorDisplayName($event);
                                        $eventInitials = $actorInitials($actorName);
                                        $fromStatusLabel = $event->from_status ? $formatStatusLabel($event->from_status) : null;
                                        $toStatusLabel = $event->to_status ? $formatStatusLabel($event->to_status) : null;
                                        $fromStatusClasses = $statusBadgeClasses[(string) $event->from_status] ?? 'bg-light text-defaulttextcolor dark:text-white/70';
                                        $toStatusClasses = $statusBadgeClasses[(string) $event->to_status] ?? 'bg-light text-defaulttextcolor dark:text-white/70';
                                    @endphp
                                    <li>
                                        <div class="timeline-time text-end">
                                            <span class="date">{{ $timelineDayLabel($event->created_at) }}</span>
                                            <span class="time inline-block">{{ $event->created_at->format('h:i A') }}</span>
                                        </div>
                                        <div class="timeline-icon">
                                            <a aria-label="timeline event" href="javascript:void(0);"></a>
                                        </div>
                                        <div class="timeline-body">
                                            <div class="flex items-start timeline-main-content flex-wrap mt-0">
                                                <div class="avatar avatar-md me-3 avatar-rounded text-primary font-semibold flex items-center justify-center md:mt-0 mt-6 task-show-timeline-avatar">
                                                    {{ $eventInitials }}
                                                </div>
                                                <div class="flex-grow min-w-0 task-show-timeline-copy">
                                                    <div class="task-show-timeline-head">
                                                        <div class="min-w-0">
                                                            <p class="mb-0 text-[.92rem] font-semibold text-defaulttextcolor dark:text-white">
                                                                {{ $actorName }}
                                                            </p>
                                                            <div class="mt-2 task-show-timeline-tags">
                                                                <span class="badge bg-primary/10 text-primary font-semibold">{{ $eventLabel }}</span>

                                                                @if($fromStatusLabel || $toStatusLabel)
                                                                    <span class="badge !bg-light text-[#8c9097] dark:text-white/50 font-semibold">Status Flow</span>
                                                                @endif

                                                                @if($fromStatusLabel)
                                                                    <span class="badge {{ $fromStatusClasses }} font-semibold">{{ $fromStatusLabel }}</span>
                                                                @endif

                                                                @if($fromStatusLabel || $toStatusLabel)
                                                                    <span class="text-[#8c9097] dark:text-white/50 text-xs">to</span>
                                                                @endif

                                                                @if($toStatusLabel)
                                                                    <span class="badge {{ $toStatusClasses }} font-semibold">{{ $toStatusLabel }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <span class="badge !bg-light text-[#8c9097] dark:text-white/50 timeline-badge task-show-timeline-stamp">
                                                            {{ $event->created_at->format('d M Y') }}
                                                        </span>
                                                    </div>

                                                    @if($event->note)
                                                        <p class="mb-0 text-sm whitespace-pre-line task-show-timeline-note">
                                                            {{ $event->note }}
                                                        </p>
                                                    @endif

                                                    @if(
                                                        $event->actor
                                                        && $event->actor_name_snapshot
                                                        && $event->actor->profile
                                                        && trim($event->actor_name_snapshot) !== trim($event->actor->profile->full_name)
                                                    )
                                                        <p class="mb-0 text-xs italic text-[#8c9097] dark:text-white/50">
                                                            Current profile name: {{ $event->actor->profile->full_name }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
