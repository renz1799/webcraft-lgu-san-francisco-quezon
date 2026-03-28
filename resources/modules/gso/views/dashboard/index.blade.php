@extends('layouts.master')

@section('content')
@php
    $statusClasses = [
        'draft' => 'bg-warning/10 text-warning',
        'submitted' => 'bg-primary/10 text-primary',
        'in_progress' => 'bg-info/10 text-info',
        'inspected' => 'bg-success/10 text-success',
        'issued' => 'bg-success/10 text-success',
        'finalized' => 'bg-success/10 text-success',
        'approved' => 'bg-info/10 text-info',
        'rejected' => 'bg-danger/10 text-danger',
        'cancelled' => 'bg-danger/10 text-danger',
    ];
@endphp

<div id="gso-dashboard-page">
    <div class="md:flex block items-center justify-between my-[1.5rem] page-header-breadcrumb">
        <div>
            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 text-[1.125rem] font-semibold mb-1">
                Operations Dashboard
            </h3>
            <p class="text-[#8c9097] dark:text-white/50 text-[0.875rem] mb-0">
                Welcome back, {{ $greetingName }}. Here is the current GSO workflow picture for {{ now()->format('F j, Y') }}.
            </p>
        </div>
        <div class="btn-list md:mt-0 mt-2">
            <a href="{{ route('gso.tasks.index', ['scope' => 'mine', 'archived' => 'active']) }}" class="ti-btn bg-primary text-white btn-wave !font-medium !rounded-[0.35rem] !py-[0.55rem] !px-[0.95rem] shadow-none">
                <i class="ri-task-line me-2 inline-block"></i>My Tasks
            </a>
            <a href="{{ route('notifications.index') }}" class="ti-btn ti-btn-outline-secondary btn-wave !font-medium !rounded-[0.35rem] !py-[0.55rem] !px-[0.95rem] shadow-none">
                <i class="ri-notification-3-line me-2 inline-block"></i>Notifications
            </a>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-x-6">
        <div class="xxl:col-span-8 col-span-12">
            <div class="box overflow-hidden !bg-primary !border-primary shadow-none">
                <div class="box-body !p-6 text-white">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <p class="uppercase tracking-[0.18em] text-white/70 text-[0.7rem] mb-2">Today at a glance</p>
                            <h4 class="text-[1.5rem] font-semibold mb-2 text-white">Focus on tasks, queues, and draft completion.</h4>
                            <p class="text-white/80 text-[0.9rem] mb-0 max-w-3xl">
                                This dashboard now lives inside the GSO module and follows the actual office workflow: action items first, then document queues, recent activity, and inventory visibility.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 min-w-[280px]">
                            <div class="rounded-md bg-white/10 border border-white/10 px-4 py-3">
                                <p class="text-[0.75rem] text-white/70 mb-1">Open tasks</p>
                                <p class="text-[1.35rem] font-semibold mb-0">{{ $taskCounts['my'] ?? 0 }}</p>
                            </div>
                            <div class="rounded-md bg-white/10 border border-white/10 px-4 py-3">
                                <p class="text-[0.75rem] text-white/70 mb-1">Claimable</p>
                                <p class="text-[1.35rem] font-semibold mb-0">{{ $taskCounts['claimable'] ?? 0 }}</p>
                            </div>
                            <div class="rounded-md bg-white/10 border border-white/10 px-4 py-3">
                                <p class="text-[0.75rem] text-white/70 mb-1">Awaiting action</p>
                                <p class="text-[1.35rem] font-semibold mb-0">{{ $documentsAwaitingAction }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="xxl:col-span-4 col-span-12">
            <div class="box">
                <div class="box-header justify-between">
                    <div class="box-title">Quick Access</div>
                    <a href="{{ route('gso.tasks.index', ['scope' => 'mine', 'archived' => 'active']) }}" class="text-primary text-[0.75rem] font-medium">View tasks</a>
                </div>
                <div class="box-body">
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($quickLinks as $link)
                            <a href="{{ $link['href'] }}" class="border dark:border-defaultborder/10 rounded-md px-3 py-3 hover:border-primary/40 hover:bg-primary/5 transition-all">
                                <div class="flex items-center gap-2">
                                    <span class="avatar avatar-sm rounded-md bg-primary/10 text-primary">
                                        <i class="{{ $link['icon'] }} text-[1rem]"></i>
                                    </span>
                                    <span class="text-[0.8125rem] font-medium text-defaulttextcolor dark:text-defaulttextcolor/70">{{ $link['label'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-x-6">
        <div class="xxl:col-span-3 xl:col-span-6 col-span-12">
            <div class="box overflow-hidden">
                <div class="box-body">
                    <div class="flex items-start justify-between gap-3">
                        <span class="avatar avatar-lg rounded-md bg-primary/10 text-primary">
                            <i class="ri-task-line text-[1.15rem]"></i>
                        </span>
                        <div class="text-end">
                            <p class="text-[#8c9097] dark:text-white/50 text-[0.75rem] mb-1">My Open Tasks</p>
                            <h4 class="font-semibold text-[1.5rem] mb-1">{{ $taskCounts['my'] ?? 0 }}</h4>
                            <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-0">Assigned tasks not yet done or cancelled.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="xxl:col-span-3 xl:col-span-6 col-span-12">
            <div class="box overflow-hidden">
                <div class="box-body">
                    <div class="flex items-start justify-between gap-3">
                        <span class="avatar avatar-lg rounded-md bg-info/10 text-info">
                            <i class="ri-stack-line text-[1.15rem]"></i>
                        </span>
                        <div class="text-end">
                            <p class="text-[#8c9097] dark:text-white/50 text-[0.75rem] mb-1">Claimable Tasks</p>
                            <h4 class="font-semibold text-[1.5rem] mb-1">{{ $taskCounts['claimable'] ?? 0 }}</h4>
                            <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-0">Available tasks matching your current GSO role.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="xxl:col-span-3 xl:col-span-6 col-span-12">
            <div class="box overflow-hidden">
                <div class="box-body">
                    <div class="flex items-start justify-between gap-3">
                        <span class="avatar avatar-lg rounded-md bg-secondary/10 text-secondary">
                            <i class="ri-notification-3-line text-[1.15rem]"></i>
                        </span>
                        <div class="text-end">
                            <p class="text-[#8c9097] dark:text-white/50 text-[0.75rem] mb-1">Unread Notifications</p>
                            <h4 class="font-semibold text-[1.5rem] mb-1">{{ $unreadNotifications }}</h4>
                            <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-0">Unread workflow and system notices in the GSO context.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="xxl:col-span-3 xl:col-span-6 col-span-12">
            <div class="box overflow-hidden">
                <div class="box-body">
                    <div class="flex items-start justify-between gap-3">
                        <span class="avatar avatar-lg rounded-md bg-warning/10 text-warning">
                            <i class="ri-draft-line text-[1.15rem]"></i>
                        </span>
                        <div class="text-end">
                            <p class="text-[#8c9097] dark:text-white/50 text-[0.75rem] mb-1">Open Draft Documents</p>
                            <h4 class="font-semibold text-[1.5rem] mb-1">{{ $openDraftDocuments }}</h4>
                            <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-0">Office-wide drafts across active GSO document workflows.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-header justify-between">
            <div class="box-title">Document Workflow Overview</div>
            <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50">Shared operational picture across current GSO document modules.</span>
        </div>
        <div class="box-body">
            <div class="grid grid-cols-12 gap-4">
                @foreach($workflowCards as $card)
                    <div class="xxl:col-span-4 xl:col-span-6 col-span-12">
                        <div class="border dark:border-defaultborder/10 rounded-md p-4 h-full">
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <div>
                                    <h5 class="font-semibold text-[1rem] mb-1">{{ $card['module'] }}</h5>
                                    <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-0">{{ $card['description'] }}</p>
                                </div>
                                <a href="{{ $card['href'] }}" class="text-primary text-[0.75rem] font-medium whitespace-nowrap">Open</a>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="rounded-md bg-light dark:bg-black/20 px-3 py-3">
                                    <p class="text-[0.7rem] uppercase tracking-[0.08em] text-[#8c9097] dark:text-white/50 mb-1">Draft</p>
                                    <p class="text-[1.1rem] font-semibold mb-0">{{ $card['draft_count'] }}</p>
                                </div>
                                <div class="rounded-md bg-primary/5 px-3 py-3">
                                    <p class="text-[0.7rem] uppercase tracking-[0.08em] text-[#8c9097] dark:text-white/50 mb-1">{{ $card['action_label'] }}</p>
                                    <p class="text-[1.1rem] font-semibold mb-0 text-primary">{{ $card['action_count'] }}</p>
                                </div>
                                <div class="rounded-md bg-success/5 px-3 py-3">
                                    <p class="text-[0.7rem] uppercase tracking-[0.08em] text-[#8c9097] dark:text-white/50 mb-1">{{ $card['done_label'] }}</p>
                                    <p class="text-[1.1rem] font-semibold mb-0 text-success">{{ $card['done_count'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-x-6">
        <div class="xxl:col-span-8 col-span-12">
            <div class="box">
                <div class="box-header justify-between">
                    <div class="box-title">Recent Documents</div>
                    <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50">Latest updated working documents across migrated GSO modules.</span>
                </div>
                <div class="box-body !p-0">
                    <div class="table-responsive">
                        <table class="table whitespace-nowrap min-w-full mb-0">
                            <thead>
                                <tr>
                                    <th class="text-start">Module</th>
                                    <th class="text-start">Reference</th>
                                    <th class="text-start">Details</th>
                                    <th class="text-start">Status</th>
                                    <th class="text-start">Updated</th>
                                    <th class="text-start">Open</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDocuments as $document)
                                    <tr class="border-y border-inherit border-solid dark:border-defaultborder/10">
                                        <td>
                                            <span class="badge bg-primary/10 text-primary">{{ $document['module'] }}</span>
                                        </td>
                                        <td class="font-medium">{{ $document['reference'] }}</td>
                                        <td class="max-w-[16rem] truncate text-[#8c9097] dark:text-white/50">{{ $document['subtext'] }}</td>
                                        <td>
                                            <span class="badge {{ $statusClasses[$document['status']] ?? 'bg-light text-defaulttextcolor' }}">
                                                {{ str_replace('_', ' ', ucfirst($document['status'])) }}
                                            </span>
                                        </td>
                                        <td class="text-[#8c9097] dark:text-white/50">{{ $document['updated_at_text'] }}</td>
                                        <td>
                                            <a href="{{ $document['url'] }}" class="text-primary font-medium text-[0.8125rem]">Open</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-[#8c9097] dark:text-white/50 py-8">No recent documents found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="xxl:col-span-4 col-span-12">
            <div class="box">
                <div class="box-header justify-between">
                    <div class="box-title">Recent Notifications</div>
                    <a href="{{ route('notifications.index') }}" class="text-primary text-[0.75rem] font-medium">View all</a>
                </div>
                <div class="box-body">
                    <ul class="list-none mb-0 space-y-4">
                        @forelse($recentNotifications as $notification)
                            <li>
                                <div class="flex items-start gap-3">
                                    <span class="avatar avatar-sm rounded-full {{ $notification['is_read'] ? 'bg-light text-defaulttextcolor' : 'bg-primary/10 text-primary' }}">
                                        <i class="ri-notification-3-line"></i>
                                    </span>
                                    <div class="flex-grow min-w-0">
                                        <div class="flex items-start justify-between gap-3">
                                            <p class="font-medium mb-1 truncate">{{ $notification['title'] }}</p>
                                            <span class="text-[0.6875rem] text-[#8c9097] dark:text-white/50 whitespace-nowrap">{{ $notification['created_at_text'] }}</span>
                                        </div>
                                        <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-0">{{ \Illuminate\Support\Str::limit($notification['message'], 110) }}</p>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-[#8c9097] dark:text-white/50 text-[0.8125rem]">No recent notifications.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-header justify-between">
            <div class="box-title">Inventory Snapshot</div>
            <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50">Current visibility across the pool, issued items, and consumable stock pressure.</span>
        </div>
        <div class="box-body">
            <div class="grid grid-cols-12 gap-4">
                @foreach($inventorySnapshot as $item)
                    <div class="xxl:col-span-3 xl:col-span-6 col-span-12">
                        <a href="{{ $item['href'] }}" class="block border dark:border-defaultborder/10 rounded-md p-4 h-full hover:border-primary/40 hover:bg-primary/5 transition-all">
                            <p class="text-[0.75rem] uppercase tracking-[0.08em] text-[#8c9097] dark:text-white/50 mb-2">{{ $item['label'] }}</p>
                            <h4 class="font-semibold text-[1.5rem] mb-2">{{ $item['value'] }}</h4>
                            <p class="text-[0.8125rem] text-[#8c9097] dark:text-white/50 mb-0">{{ $item['helper'] }}</p>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
