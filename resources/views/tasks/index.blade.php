@extends('layouts.master')

@section('content')
<div class="container mx-auto px-4 py-4">

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-defaulttextcolor dark:text-white">Tasks</h1>
        <a href="{{ url('notifications') }}" class="ti-btn ti-btn-light">Notifications</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded bg-success/10 text-success text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- My Tasks --}}
        <div class="bg-white dark:bg-bgdark rounded-lg border border-defaultborder p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-[1rem] font-semibold text-defaulttextcolor dark:text-white">My Tasks</h2>
            </div>

            @if($myTasks->count() === 0)
                <p class="text-sm text-[#8c9097]">No assigned tasks.</p>
            @else
                <div class="space-y-2">
                    @foreach($myTasks as $task)
                        <a href="{{ route('tasks.show', $task->id) }}"
                           class="block p-3 rounded border border-defaultborder hover:bg-primary/5 transition">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0">
                                    <div class="font-semibold text-sm text-defaulttextcolor dark:text-white truncate">
                                        {{ $task->title }}
                                    </div>
                                    <div class="text-xs text-[#8c9097] truncate">
                                        Status: {{ $task->status }}
                                        @if($task->due_at)
                                            • Due: {{ $task->due_at->format('M d, Y') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="text-xs px-2 py-1 rounded bg-secondary/10 text-secondary">
                                    {{ strtoupper($task->status) }}
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $myTasks->links() }}
                </div>
            @endif
        </div>

        {{-- Available Tasks (Role-based pooled tasks) --}}
        <div class="bg-white dark:bg-bgdark rounded-lg border border-defaultborder p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-[1rem] font-semibold text-defaulttextcolor dark:text-white">Available Tasks</h2>
                <span class="text-xs text-[#8c9097]">Claimable by your role</span>
            </div>

            @if(($availableTasks ?? collect())->count() === 0)
                <p class="text-sm text-[#8c9097]">No available tasks for your roles.</p>
            @else
                <div class="space-y-2">
                    @foreach($availableTasks as $task)
                        <div class="p-3 rounded border border-defaultborder">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold text-sm text-defaulttextcolor dark:text-white truncate">
                                        {{ $task->title }}
                                    </div>
                                    <div class="text-xs text-[#8c9097]">
                                        Status: {{ $task->status }}
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('tasks.claim', $task->id) }}">
                                    @csrf
                                    <button type="submit" class="ti-btn ti-btn-primary ti-btn-sm">
                                        Claim
                                    </button>
                                </form>
                            </div>

                            @php($subjectUrl = data_get($task->data, 'subject_url'))
                            @if($subjectUrl)
                                <div class="mt-2 text-xs">
                                    <a class="text-primary hover:underline" href="{{ $subjectUrl }}">
                                        View Related Record
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
