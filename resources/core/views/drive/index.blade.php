@extends('layouts.master')

@section('content')
    <div class="page-header md:flex items-start justify-between gap-4">
        <div>
            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
                Google Drive Integrations
            </h3>
            <p class="text-xs text-[#8c9097] mt-1">
                Core Platform manages Google Drive connections for each platform or module context. Workflow pages like AIR use the token stored for their owning module scope.
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

    <div class="box">
        <div class="box-header">
            <div>
                <h5 class="box-title">Connection Registry</h5>
                <div class="text-xs text-[#8c9097] mt-1">
                    Tokens are stored per <strong>module + default department</strong> scope. Connect the context that owns the workflow, not just the page you are viewing.
                </div>
            </div>
        </div>

        <div class="box-body">
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
        </div>
    </div>
@endsection
