@extends('layouts.master')

@section('content')
@php
    $statusClasses = [
        'pending' => 'bg-warning/10 text-warning',
        'approved' => 'bg-success/10 text-success',
        'rejected' => 'bg-danger/10 text-danger',
    ];
@endphp

<div class="block justify-between page-header md:flex">
    <div>
        <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 text-[1.125rem] font-semibold">
            Identity Change Approvals
        </h3>
        <p class="text-sm text-[#8c9097] dark:text-white/50 mb-0">
            Review and approve platform identity updates before official profile records are changed.
        </p>
    </div>
    <ol class="flex items-center whitespace-nowrap min-w-0">
        <li class="text-[0.813rem] ps-[0.5rem]">
            <a class="flex items-center text-primary hover:text-primary truncate" href="{{ route('access.users.index') }}">
                Access
                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] px-[0.5rem] overflow-visible rtl:rotate-180"></i>
            </a>
        </li>
        <li class="text-[0.813rem] text-defaulttextcolor font-semibold" aria-current="page">
            Identity Change Requests
            
        </li>
    </ol>
</div>

<div class="box">
    @if (session('success'))
        <div class="p-4 border-b border-defaultborder dark:border-defaultborder/10">
            <div class="alert alert-success !mb-0">
                {{ session('success') }}
            </div>
        </div>
    @endif
    <div class="box-header justify-between items-center sm:flex block">
        <div>
            <div class="box-title">Review Queue</div>
            <p class="text-xs text-[#8c9097] dark:text-white/50 mt-1 mb-0">
                Pending requests can be reviewed here or claimed from the administrator task queue.
            </p>
        </div>
        <form method="GET" action="{{ route('identity-change-requests.index') }}" class="grid grid-cols-12 gap-3 w-full sm:w-auto mt-4 sm:mt-0 sm:min-w-[32rem]">
            <div class="col-span-12 sm:col-span-6">
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    class="form-control w-full !rounded-md"
                    placeholder="Search by requester, email, username, or requested name">
            </div>
            <div class="col-span-8 sm:col-span-4">
                <select name="status" class="form-control w-full !rounded-md">
                    @foreach ($statusOptions as $code => $label)
                        <option value="{{ $code }}" @selected($filters['status'] === $code)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-4 sm:col-span-2 flex gap-2">
                <button type="submit" class="ti-btn btn-wave bg-primary text-white w-full">Filter</button>
            </div>
        </form>
    </div>
    <div class="box-body">
        <div class="overflow-x-auto">
            <table class="table whitespace-nowrap min-w-full">
                <thead>
                    <tr>
                        <th>Requester</th>
                        <th>Current Name</th>
                        <th>Requested Name</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Reviewed</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $requestItem)
                        @php
                            $requesterName = trim((string) ($requestItem->user?->profile?->full_name ?? '')) ?: ($requestItem->user?->username ?: $requestItem->user?->email ?: 'User');
                            $status = (string) $requestItem->status;
                        @endphp
                        <tr>
                            <td class="align-top">
                                <div class="font-semibold">{{ $requesterName }}</div>
                                <div class="text-xs text-[#8c9097] dark:text-white/50">
                                    {{ $requestItem->user?->email ?: 'No email' }}
                                </div>
                            </td>
                            <td class="align-top">{{ $requestItem->currentFullName() ?: 'No recorded identity' }}</td>
                            <td class="align-top">{{ $requestItem->requestedFullName() ?: 'No requested identity' }}</td>
                            <td class="align-top">
                                <span class="badge {{ $statusClasses[$status] ?? 'bg-light text-defaulttextcolor' }}">
                                    {{ str($status)->replace('_', ' ')->title() }}
                                </span>
                            </td>
                            <td class="align-top">{{ optional($requestItem->created_at)->format('M d, Y') }}</td>
                            <td class="align-top">
                                {{ optional($requestItem->reviewed_at)->format('M d, Y') ?: 'Not reviewed' }}
                            </td>
                            <td class="align-top text-end">
                                <a href="{{ route('identity-change-requests.show', $requestItem->id) }}"
                                   class="ti-btn ti-btn-light btn-wave !px-3 !py-2">
                                    {{ $requestItem->isPending() ? 'Open Approval' : 'View Decision' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-[#8c9097] dark:text-white/50">
                                No identity change requests match the current filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($requests->hasPages())
        <div class="box-footer">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection
