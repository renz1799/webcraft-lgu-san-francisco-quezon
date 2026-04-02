@extends('layouts.master')

@section('content')
    <div class="page-header md:flex items-start justify-between gap-4">
        <div>
            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
                Workflow Notification Rules
            </h3>
            <p class="text-xs text-[#8c9097] mt-1">
                Configure which module roles are notified for workflow events without editing service code every time the routing policy changes.
            </p>
        </div>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded border border-success bg-success/10 px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="box overflow-hidden">
        <div class="box-header !block">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h5 class="box-title">Module Workflow Notifications</h5>
                    <div class="text-xs text-[#8c9097] mt-1">
                        Rules are separated by module so future office systems like DTS can keep their own workflow fan-out without disturbing GSO.
                    </div>
                </div>

                <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                    Core Platform Configuration
                </span>
            </div>
        </div>

        <div class="box-body">
            <div class="space-y-4">
                @forelse($notificationContexts as $notificationContext)
                    @php
                        $module = $notificationContext['module'];
                    @endphp

                    <form method="POST" action="{{ route('workflow-notifications.update') }}" class="box !mb-0">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="module_id" value="{{ $module->id }}">

                        <div class="box-header !block">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                <div class="max-w-4xl">
                                    <h5 class="box-title">{{ $notificationContext['title'] }}</h5>
                                    <div class="text-xs text-[#8c9097] mt-1">
                                        {{ $notificationContext['description'] ?: 'Configure workflow notification recipients for this module.' }}
                                    </div>

                                    @if(!empty($notificationContext['notes']))
                                        <div class="mt-3 rounded border border-primary/10 bg-primary/5 px-3 py-2 text-xs text-[#5f6782] dark:border-primary/10 dark:bg-primary/10 dark:text-white/60">
                                            <div class="font-medium text-defaulttextcolor dark:text-white/80">Configuration Notes</div>
                                            <div class="mt-2 space-y-1.5">
                                                @foreach($notificationContext['notes'] as $note)
                                                    <div>{{ $note }}</div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-2.5 py-1 text-xs text-primary">
                                    {{ $module->name }}
                                </span>
                            </div>
                        </div>

                        <div class="box-body space-y-4">
                            @forelse($notificationContext['events'] as $event)
                                @php
                                    $oldEvents = old('events', []);
                                    $oldEventPayload = is_array($oldEvents)
                                        ? ($oldEvents[$event['key']] ?? null)
                                        : null;
                                    $selectedRoles = is_array($oldEventPayload)
                                        ? ($oldEventPayload['roles'] ?? [])
                                        : $event['effective_roles'];
                                    $selectedRoles = is_array($selectedRoles) ? $selectedRoles : [];
                                    $selectedMessageTemplate = is_array($oldEventPayload)
                                        ? trim((string) ($oldEventPayload['message_template'] ?? ''))
                                        : (string) ($event['effective_message_template'] ?? '');
                                @endphp
                                <div class="rounded border border-defaultborder dark:border-defaultborder/10 p-4">
                                    <input type="hidden" name="events[{{ $event['key'] }}][event_key]" value="{{ $event['key'] }}">

                                    <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                                        <div class="max-w-3xl">
                                            <div class="font-medium text-defaulttextcolor dark:text-white">
                                                {{ $event['label'] }}
                                            </div>
                                            @if($event['description'])
                                                <div class="mt-1 text-xs text-[#8c9097]">
                                                    {{ $event['description'] }}
                                                </div>
                                            @endif
                                        </div>

                                        <span class="inline-flex items-center rounded-full border border-defaultborder bg-light px-2.5 py-1 text-[11px] text-[#667085] dark:border-defaultborder/10 dark:bg-black/20 dark:text-white/50">
                                            {{ $event['source'] }}
                                        </span>
                                    </div>

                                    <div class="mt-3 grid grid-cols-1 gap-3 xl:grid-cols-2">
                                        <div class="rounded border border-dashed border-defaultborder bg-light/60 p-3 dark:border-defaultborder/10 dark:bg-black/20">
                                            <div class="text-[11px] font-medium uppercase tracking-wide text-[#667085] dark:text-white/50">
                                                Default Roles
                                            </div>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @forelse($event['default_roles'] as $roleName)
                                                    <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-2.5 py-1 text-[11px] font-medium text-primary">
                                                        {{ $roleName }}
                                                    </span>
                                                @empty
                                                    <span class="text-xs text-[#8c9097]">No default roles configured.</span>
                                                @endforelse
                                            </div>
                                        </div>

                                        <div class="rounded border border-dashed border-defaultborder bg-light/60 p-3 dark:border-defaultborder/10 dark:bg-black/20">
                                            <div class="text-[11px] font-medium uppercase tracking-wide text-[#667085] dark:text-white/50">
                                                Effective Roles
                                            </div>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @forelse($event['effective_roles'] as $roleName)
                                                    <span class="inline-flex items-center rounded-full border border-success/20 bg-success/10 px-2.5 py-1 text-[11px] font-medium text-success">
                                                        {{ $roleName }}
                                                    </span>
                                                @empty
                                                    <span class="text-xs text-danger">Notifications are disabled for this event.</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <div class="text-[11px] font-medium uppercase tracking-wide text-[#667085] dark:text-white/50">
                                            Roles To Notify
                                        </div>
                                        <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                                            @forelse($event['available_roles'] as $roleName)
                                                <label class="flex items-start gap-3 rounded border border-defaultborder p-3 text-sm dark:border-defaultborder/10 {{ $canUpdateWorkflowNotifications ? 'cursor-pointer hover:border-primary/30' : 'opacity-80' }}">
                                                    <input
                                                        type="checkbox"
                                                        name="events[{{ $event['key'] }}][roles][]"
                                                        value="{{ $roleName }}"
                                                        class="mt-0.5 form-check-input"
                                                        @checked(in_array($roleName, $selectedRoles, true))
                                                        @disabled(! $canUpdateWorkflowNotifications)
                                                    >
                                                    <span>
                                                        <span class="font-medium text-defaulttextcolor dark:text-white">{{ $roleName }}</span>
                                                        <span class="mt-1 block text-xs text-[#8c9097]">
                                                            Notify everyone in the {{ $roleName }} module role when this workflow event fires.
                                                        </span>
                                                    </span>
                                                </label>
                                            @empty
                                                <div class="rounded border border-dashed border-defaultborder px-3 py-4 text-sm text-[#8c9097] dark:border-defaultborder/10 dark:text-white/50 md:col-span-2 xl:col-span-3">
                                                    No active module roles are available yet. Seed the module roles first, then return here to assign notification recipients.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <div class="text-[11px] font-medium uppercase tracking-wide text-[#667085] dark:text-white/50">
                                            Notification Message
                                        </div>

                                        <div class="mt-3 grid grid-cols-1 gap-3 xl:grid-cols-2">
                                            <div class="rounded border border-dashed border-defaultborder bg-light/60 p-3 dark:border-defaultborder/10 dark:bg-black/20">
                                                <div class="text-[11px] font-medium uppercase tracking-wide text-[#667085] dark:text-white/50">
                                                    Default Message
                                                </div>
                                                <div class="mt-2 text-sm text-defaulttextcolor dark:text-white/80 whitespace-pre-line">
                                                    {{ $event['default_message_template'] !== '' ? $event['default_message_template'] : 'No default message template configured.' }}
                                                </div>
                                            </div>

                                            <div class="rounded border border-dashed border-defaultborder bg-light/60 p-3 dark:border-defaultborder/10 dark:bg-black/20">
                                                <div class="text-[11px] font-medium uppercase tracking-wide text-[#667085] dark:text-white/50">
                                                    Available Placeholders
                                                </div>
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    @forelse($event['placeholders'] as $placeholder => $placeholderDescription)
                                                        <span
                                                            class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-2.5 py-1 text-[11px] font-medium text-primary"
                                                            title="{{ $placeholderDescription }}"
                                                        >
                                                            {{ $placeholder }}
                                                        </span>
                                                    @empty
                                                        <span class="text-xs text-[#8c9097]">No placeholders configured for this event.</span>
                                                    @endforelse
                                                </div>
                                                @if(!empty($event['placeholders']))
                                                    <div class="mt-2 space-y-1 text-xs text-[#8c9097]">
                                                        @foreach($event['placeholders'] as $placeholder => $placeholderDescription)
                                                            <div><span class="font-medium text-defaulttextcolor dark:text-white/80">{{ $placeholder }}</span> {{ $placeholderDescription }}</div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <label class="form-label text-sm font-medium text-defaulttextcolor dark:text-white">
                                                Message Template
                                            </label>
                                            <textarea
                                                name="events[{{ $event['key'] }}][message_template]"
                                                rows="4"
                                                class="form-control"
                                                placeholder="{{ $event['default_message_template'] }}"
                                                @disabled(! $canUpdateWorkflowNotifications)
                                            >{{ $selectedMessageTemplate }}</textarea>
                                            <div class="mt-2 text-xs text-[#8c9097]">
                                                Leave this matching the default message to keep the seeded wording. You can use the placeholders above to inject AIR numbers, PO numbers, task links, and similar values.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded border border-dashed border-defaultborder p-6 text-sm text-[#8c9097] dark:border-defaultborder/10 dark:text-white/50">
                                    No workflow notification events are configured for this module yet.
                                </div>
                            @endforelse

                            <div class="flex flex-wrap items-center justify-between gap-3 rounded bg-light p-3 text-xs text-[#8c9097] dark:bg-black/20 dark:text-white/50">
                                <div>
                                    Saving the same role set as the default seeded rule returns the event to its default configuration. Saving with no roles selected disables notifications for that event.
                                </div>

                                @if($canUpdateWorkflowNotifications)
                                    <button type="submit" class="ti-btn ti-btn-primary !py-2 !px-4 !text-sm">
                                        Save Notification Rules
                                    </button>
                                @else
                                    <span class="inline-flex items-center rounded-full border border-defaultborder bg-white px-3 py-1 text-xs text-[#667085] dark:border-defaultborder/10 dark:bg-black/20 dark:text-white/50">
                                        View only
                                    </span>
                                @endif
                            </div>
                        </div>
                    </form>
                @empty
                    <div class="rounded border border-dashed border-defaultborder p-6 text-sm text-[#8c9097] dark:border-defaultborder/10 dark:text-white/50">
                        No workflow notification profiles are configured yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
