@extends('layouts.master')

@section('content')
    @php
        $isConnectionsTab = ($activeTab ?? 'connections') !== 'storage';
    @endphp

    <div class="page-header md:flex items-start justify-between gap-4">
        <div>
            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
                Google Drive Integrations
            </h3>
            <p class="text-xs text-[#8c9097] mt-1">
                Core Platform manages Google Drive connections and module storage roots for each workflow context.
            </p>
        </div>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded border border-success bg-success/10 px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded border border-danger bg-danger/10 px-4 py-3 text-sm text-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="box overflow-hidden">
        <div class="box-header !block">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h5 class="box-title">Drive Administration</h5>
                    <div class="text-xs text-[#8c9097] mt-1">
                        Keep module Drive tokens and module-specific folder roots in sync from one platform workspace.
                    </div>
                </div>

                <div class="inline-flex rounded-md bg-light p-1 dark:bg-black/20">
                    <a href="{{ route('drive.index', ['tab' => 'connections']) }}"
                       class="rounded-md px-3 py-2 text-xs font-medium {{ $isConnectionsTab ? 'bg-primary text-white shadow-sm' : 'text-defaulttextcolor dark:text-white/70' }}">
                        Connections
                    </a>
                    <a href="{{ route('drive.index', ['tab' => 'storage']) }}"
                       class="rounded-md px-3 py-2 text-xs font-medium {{ $isConnectionsTab ? 'text-defaulttextcolor dark:text-white/70' : 'bg-primary text-white shadow-sm' }}">
                        Module Storage
                    </a>
                </div>
            </div>
        </div>

        <div class="box-body">
            @if($isConnectionsTab)
                <div class="overflow-x-auto">
                    <table class="table whitespace-nowrap min-w-full">
                        <thead>
                            <tr>
                                <th>Context</th>
                                <th>Type</th>
                                <th>Department Scope</th>
                                <th>Status</th>
                                <th>Connected By</th>
                                <th>Updated</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contexts as $context)
                                @php
                                    $statusClasses = $context['connected']
                                        ? 'bg-success/10 text-success border-success/20'
                                        : ($context['is_connectable']
                                            ? 'bg-warning/10 text-warning border-warning/20'
                                            : 'bg-danger/10 text-danger border-danger/20');
                                    $statusText = $context['connected']
                                        ? 'Connected'
                                        : ($context['is_connectable'] ? 'Not Connected' : 'Unavailable');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="font-medium text-defaulttextcolor dark:text-white">
                                            {{ $context['module_name'] }}
                                        </div>
                                        <div class="text-xs text-[#8c9097] mt-1">
                                            {{ $context['module_code'] }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs {{ $context['module_type'] === 'Platform' ? 'border-primary/20 bg-primary/10 text-primary' : 'border-defaultborder bg-light text-defaulttextcolor dark:bg-black/20 dark:text-white/70' }}">
                                            {{ $context['module_type'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($context['department_name'])
                                            <div class="font-medium text-defaulttextcolor dark:text-white">
                                                {{ $context['department_name'] }}
                                            </div>
                                            <div class="text-xs text-[#8c9097] mt-1">
                                                {{ $context['department_code'] ?? 'No code' }}
                                            </div>
                                        @else
                                            <div class="text-sm text-danger">
                                                No default department scope configured
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs {{ $statusClasses }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-sm text-defaulttextcolor dark:text-white/80">
                                            {{ $context['connected_by_name'] ?: '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-sm text-defaulttextcolor dark:text-white/80">
                                            {{ $context['connected_at_text'] ?: '—' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($context['is_connectable'])
                                            <div class="flex flex-wrap justify-end gap-2">
                                                <form method="POST" action="{{ route('drive.connect') }}">
                                                    @csrf
                                                    <input type="hidden" name="module_id" value="{{ $context['module_id'] }}">
                                                    <button type="submit" class="ti-btn ti-btn-primary !py-1.5 !px-3 !text-xs">
                                                        {{ $context['connected'] ? 'Reconnect' : 'Connect' }}
                                                    </button>
                                                </form>

                                                @if($context['connected'])
                                                    <form method="POST" action="{{ route('drive.disconnect') }}">
                                                        @csrf
                                                        <input type="hidden" name="module_id" value="{{ $context['module_id'] }}">
                                                        <button type="submit" class="ti-btn ti-btn-danger !py-1.5 !px-3 !text-xs">
                                                            Disconnect
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-[#8c9097]">
                                                Configure the module department scope first.
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-sm text-[#8c9097] py-6">
                                        No active integration contexts are available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 rounded bg-light p-3 text-xs text-[#8c9097] dark:bg-black/20 dark:text-white/50">
                    If a module page shows <strong>Google Drive is not connected for the current module context</strong>, connect that module here. Example: AIR uploads inside GSO require a connected <strong>GSO</strong> Drive scope, not just a CORE connection.
                </div>
            @else
                <div class="space-y-4">
                    @forelse($storageContexts as $storageContext)
                        @php
                            $module = $storageContext['module'];
                        @endphp

                        <form method="POST" action="{{ route('drive.storage.update') }}" class="box !mb-0">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="module_id" value="{{ $module->id }}">

                            <div class="box-header !block">
                                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                    <div class="max-w-3xl">
                                        <h5 class="box-title">{{ $storageContext['title'] }}</h5>
                                        <div class="text-xs text-[#8c9097] mt-1">
                                            {{ $storageContext['description'] ?: 'Manage module-specific Google Drive root folders.' }}
                                        </div>

                                        @if(!empty($storageContext['notes']))
                                            <div class="mt-3 rounded border border-primary/10 bg-primary/5 px-3 py-2 text-xs text-[#5f6782] dark:border-primary/10 dark:bg-primary/10 dark:text-white/60">
                                                <div class="font-medium text-defaulttextcolor dark:text-white/80">Storage Rules</div>
                                                <div class="mt-2 space-y-1.5">
                                                    @foreach($storageContext['notes'] as $note)
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
                                @foreach($storageContext['fields'] as $field)
                                    <div class="rounded border border-defaultborder dark:border-defaultborder/10 p-4">
                                        <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_340px]">
                                            <div>
                                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                    <label class="ti-form-label !mb-0">{{ $field['label'] }}</label>
                                                    <span class="inline-flex items-center rounded-full border border-defaultborder bg-light px-2.5 py-1 text-[11px] text-[#667085] dark:border-defaultborder/10 dark:bg-black/20 dark:text-white/50">
                                                        {{ $field['source'] }}
                                                    </span>
                                                </div>

                                                @if($field['help'])
                                                    <div class="mt-2 text-xs text-[#8c9097]">
                                                        {{ $field['help'] }}
                                                    </div>
                                                @endif

                                                <input
                                                    type="text"
                                                    name="storage[{{ $field['key'] }}]"
                                                    class="form-control mt-3"
                                                    value="{{ old('storage.' . $field['key'], $field['stored_value']) }}"
                                                    placeholder="{{ $field['effective_value'] ?: 'Folder ID not configured' }}"
                                                    autocomplete="off"
                                                >

                                                <div class="mt-3 grid grid-cols-1 gap-2 text-xs text-[#8c9097] md:grid-cols-2">
                                                    <div>
                                                        Stored value:
                                                        <span class="font-medium text-defaulttextcolor dark:text-white/80">
                                                            {{ $field['stored_value'] ?: 'Not stored' }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        Effective value:
                                                        <span class="font-medium text-defaulttextcolor dark:text-white/80">
                                                            {{ $field['effective_value'] ?: 'Not configured' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="rounded border border-dashed border-defaultborder bg-light/60 p-3 dark:border-defaultborder/10 dark:bg-black/20">
                                                <div class="text-xs font-medium uppercase tracking-wide text-[#667085] dark:text-white/50">
                                                    Expected Structure
                                                </div>

                                                @if(!empty($field['examples']))
                                                    <div class="mt-3 space-y-2 text-xs text-[#8c9097]">
                                                        @foreach($field['examples'] as $example)
                                                            <div class="rounded bg-white px-2.5 py-2 font-mono text-[11px] text-defaulttextcolor shadow-sm dark:bg-black/20 dark:text-white/70">
                                                                {{ $example }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="mt-3 text-xs text-[#8c9097]">
                                                        No example path is configured for this root yet.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="flex flex-wrap justify-between gap-3 rounded bg-light p-3 text-xs text-[#8c9097] dark:bg-black/20 dark:text-white/50">
                                    <div>
                                        Leave a field blank and save if you want this module to fall back to the legacy config or the global Drive default while you finish the folder migration.
                                    </div>

                                    <button type="submit" class="ti-btn ti-btn-primary !py-2 !px-4 !text-sm">
                                        Save Storage Roots
                                    </button>
                                </div>
                            </div>
                        </form>
                    @empty
                        <div class="rounded border border-dashed border-defaultborder p-6 text-sm text-[#8c9097] dark:border-defaultborder/10 dark:text-white/50">
                            No module storage profiles are configured yet.
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
@endsection
