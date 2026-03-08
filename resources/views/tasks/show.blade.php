@extends('layouts.master')

@section('content')
<div id="task-show-page" class="w-full px-4 py-4 max-w-[1600px]">


    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-semibold text-defaulttextcolor dark:text-white">{{ $task->title }}</h1>
            <p class="text-sm text-[#8c9097]">
                Status: <span class="font-semibold">{{ $task->status }}</span>
                â€¢ Created: {{ $task->created_at->format('M d, Y h:i A') }}
            </p>
        </div>
        <div class="flex gap-2 flex-wrap justify-end">
            <a href="{{ route('tasks.index') }}" class="ti-btn ti-btn-light">Back</a>

            @if($subjectUrl)
                <a href="{{ $subjectUrl }}" class="ti-btn ti-btn-secondary">Open Related</a>
            @endif

            @foreach(($headerActions ?? []) as $action)
                @php
                    $actionType = ($action['type'] ?? 'link') === 'button' ? 'button' : 'link';
                    $actionLabel = trim((string) ($action['label'] ?? ''));
                    $actionClasses = trim((string) ($action['classes'] ?? 'ti-btn ti-btn-secondary'));
                    $actionHref = (string) ($action['href'] ?? '#');
                    $actionAttributes = is_array($action['attributes'] ?? null) ? $action['attributes'] : [];
                    $actionButtonType = (string) ($action['button_type'] ?? 'button');
                @endphp

                @if($actionLabel !== '')
                    @if($actionType === 'link')
                        <a href="{{ $actionHref }}" class="{{ $actionClasses }}"
                            @foreach($actionAttributes as $key => $value)
                                @php($attrName = is_string($key) ? trim($key) : '')
                                @if($attrName !== '' && preg_match('/^[a-zA-Z0-9_:\-]+$/', $attrName))
                                    {{ $attrName }}="{{ e((string) $value) }}"
                                @endif
                            @endforeach
                        >
                            {{ $actionLabel }}
                        </a>
                    @else
                        <button type="{{ $actionButtonType }}" class="{{ $actionClasses }}"
                            @foreach($actionAttributes as $key => $value)
                                @php($attrName = is_string($key) ? trim($key) : '')
                                @if($attrName !== '' && preg_match('/^[a-zA-Z0-9_:\-]+$/', $attrName))
                                    {{ $attrName }}="{{ e((string) $value) }}"
                                @endif
                            @endforeach
                        >
                            {{ $actionLabel }}
                        </button>
                    @endif
                @endif
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Left: Details + Actions --}}
        <div class="lg:col-span-1 bg-white dark:bg-bgdark rounded-lg border border-defaultborder p-4">

            <div class="mb-3">
                <div class="text-sm text-[#8c9097]">Assigned To</div>
                <div class="font-semibold text-defaulttextcolor dark:text-white">
                    {{ $task->assignedTo->profile->full_name ?? 'Unassigned' }}
                </div>
            </div>

            @if($task->description)
                <div class="mb-3">
                    <div class="text-sm text-[#8c9097]">Description</div>
                    <div class="text-sm text-defaulttextcolor dark:text-white whitespace-pre-line">
                        {{ $task->description }}
                    </div>
                </div>
            @endif

            {{-- Claim (if pooled) --}}
            @can('claim', $task)
                <form method="POST" action="{{ route('tasks.claim', $task->id) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="ti-btn ti-btn-primary w-full">Claim Task</button>
                </form>
            @endcan

            {{-- Status Actions --}}
            @can('updateStatus', $task)
            <div class="border-t pt-3 mt-3">
                <div class="font-semibold text-defaulttextcolor dark:text-white mb-2">Actions</div>

                <form method="POST"
                    action="{{ route('tasks.status.update', $task->id) }}"
                    class="space-y-2 js-task-status-form">
                    @csrf
                    <select name="status" class="ti-form-select w-full">
                        <option value="pending" @selected($task->status === 'pending')>Pending</option>
                        <option value="in_progress" @selected($task->status === 'in_progress')>In Progress</option>
                        <option value="done" @selected($task->status === 'done')>Done</option>
                        <option value="cancelled" @selected($task->status === 'cancelled')>Cancelled</option>
                    </select>

                    <textarea name="note" rows="3" class="ti-form-input w-full"
                        placeholder="Optional note (e.g., need paper / ready to pick up / cancel reason)"></textarea>

                    <button type="submit" class="ti-btn ti-btn-secondary w-full">Update Status</button>
                </form>
            </div>
            @endcan

            {{-- Reassign (Admin + permission only) --}}
        @php($canReassign = auth()->user()?->hasRole('Administrator') || auth()->user()?->can('modify Reassign Tasks'))

        @if($canReassign)
        <div class="border-t pt-3 mt-3">
            <div class="font-semibold text-defaulttextcolor dark:text-white mb-2">Reassign Task</div>

            <form method="POST"
                action="{{ route('tasks.reassign', $task->id) }}"
                class="space-y-2 js-task-reassign-form">
            @csrf

            <select name="assignee_user_id" class="ti-form-select w-full" required>
                <option value="" disabled selected>Select new assigneeâ€¦</option>

                @foreach(($assignees ?? []) as $u)
                @php($isCurrent = (string) $task->assigned_to_user_id === (string) $u['id'])
                <option value="{{ $u['id'] }}" @disabled($isCurrent)>
                    {{ $u['name'] }} @if($isCurrent) (current) @endif
                </option>
                @endforeach
            </select>

            <textarea name="note" rows="3" class="ti-form-input w-full"
                placeholder="Optional note (reason / instructions)"></textarea>

            <button type="submit" class="ti-btn ti-btn-warning w-full">Reassign</button>
            </form>
        </div>
        @endif


            {{-- Comment --}}
            @can('comment', $task)
            <div class="border-t pt-3 mt-3">
                <div class="font-semibold text-defaulttextcolor dark:text-white mb-2">Add Comment</div>

                <form method="POST"
                    action="{{ route('tasks.comment.store', $task->id) }}"
                    class="space-y-2 js-task-comment-form">
                    @csrf
                    <textarea name="note" rows="3" class="ti-form-input w-full" required
                        placeholder="Write a comment..."></textarea>
                    <button type="submit" class="ti-btn ti-btn-light w-full">Post Comment</button>
                </form>
            </div>
        </div>
        @endcan
        

            {{-- Right: Timeline --}}
            <div class="lg:col-span-2 bg-white dark:bg-bgdark rounded-lg border border-defaultborder p-4 flex flex-col">

                <div class="flex items-center justify-between mb-3 shrink-0">
                    <h2 class="text-[1rem] font-semibold text-defaulttextcolor dark:text-white">
                        Timeline
                    </h2>
                    <span class="text-xs text-[#8c9097]">
                        {{ $events->count() }} event(s)
                    </span>
                </div>

                @if($events->count() === 0)
                    <p class="text-sm text-[#8c9097]">No events yet.</p>
                @else
                    {{-- Scrollable area --}}
                    <div class="space-y-3 overflow-y-auto pr-2"
                        style="max-height: 800px;">
                        @foreach($events as $event)
                            <div class="p-3 rounded border border-defaultborder">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">
                                        {{ strtoupper($event->event_type) }}
                                        @if($event->from_status || $event->to_status)
                                            <span class="text-xs text-[#8c9097] font-normal">
                                                ({{ $event->from_status ?? 'â€”' }} â†’ {{ $event->to_status ?? 'â€”' }})
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-[#8c9097]">
                                        {{ $event->created_at->format('M d, Y h:i A') }}
                                    </div>
                                </div>
                                @if($event->note)
                                    <div class="mt-2 text-sm text-defaulttextcolor dark:text-white whitespace-normal">
                                        {!! nl2br(e($event->note)) !!}
                                    </div>
                                @endif

                                <div class="mt-2 text-xs text-[#8c9097]">
                                    Actor:
                                    <span class="font-medium text-defaulttextcolor dark:text-white">
                                        {{ 
                                            $event->actor_name_snapshot
                                            ?? $event->actor_username_snapshot
                                            ?? optional($event->actor?->profile)->full_name
                                            ?? 'System'
                                        }}
                                    </span>
                                        {{-- Optional: show current name if different --}}
                                        @if(
                                            $event->actor
                                            && $event->actor_name_snapshot
                                            && $event->actor->profile
                                            && trim($event->actor_name_snapshot) !== trim($event->actor->profile->full_name)
                                        )
                                            <span class="italic text-[0.65rem] text-[#8c9097]">
                                                (now {{ $event->actor->profile->full_name }})
                                            </span>
                                        @endif
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

    </div>

</div>
@endsection



