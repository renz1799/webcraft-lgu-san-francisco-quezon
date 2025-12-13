@extends('layouts.master')

@section('content')
<div class="container mx-auto px-4 py-4">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-semibold text-defaulttextcolor dark:text-white">{{ $task->title }}</h1>
            <p class="text-sm text-[#8c9097]">
                Status: <span class="font-semibold">{{ $task->status }}</span>
                • Created: {{ $task->created_at->format('M d, Y h:i A') }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tasks.index') }}" class="ti-btn ti-btn-light">Back</a>

            @if($subjectUrl)
                <a href="{{ $subjectUrl }}" class="ti-btn ti-btn-secondary">Open Related</a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Left: Details + Actions --}}
        <div class="lg:col-span-1 bg-white dark:bg-bgdark rounded-lg border border-defaultborder p-4">

            <div class="mb-3">
                <div class="text-sm text-[#8c9097]">Assigned To</div>
                <div class="font-semibold text-defaulttextcolor dark:text-white">
                    {{ $task->assigned_to_user_id ? $task->assigned_to_user_id : 'Unassigned (Claimable)' }}
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
            @if(empty($task->assigned_to_user_id))
                <form method="POST" action="{{ route('tasks.claim', $task->id) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="ti-btn ti-btn-primary w-full">Claim Task</button>
                </form>
            @endif

            {{-- Status Actions --}}
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

            {{-- Comment --}}
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
                        style="max-height: 520px;">
                        @foreach($events as $event)
                            <div class="p-3 rounded border border-defaultborder">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">
                                        {{ strtoupper($event->event_type) }}
                                        @if($event->from_status || $event->to_status)
                                            <span class="text-xs text-[#8c9097] font-normal">
                                                ({{ $event->from_status ?? '—' }} → {{ $event->to_status ?? '—' }})
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-[#8c9097]">
                                        {{ $event->created_at->format('M d, Y h:i A') }}
                                    </div>
                                </div>

                                @if($event->note)
                                    <div class="mt-2 text-sm text-defaulttextcolor dark:text-white whitespace-pre-line">
                                        {{ $event->note }}
                                    </div>
                                @endif

                                <div class="mt-2 text-xs text-[#8c9097]">
                                    Actor: {{ $event->actor_user_id }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

    </div>

</div>

@push('scripts')
    @vite('resources/js/tasks/show.js')
@endpush

@endsection
