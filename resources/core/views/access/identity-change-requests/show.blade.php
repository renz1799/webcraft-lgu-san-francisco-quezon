@extends('layouts.master')

@section('content')
@php
    $statusClasses = [
        'pending' => 'bg-warning/10 text-warning',
        'approved' => 'bg-success/10 text-success',
        'rejected' => 'bg-danger/10 text-danger',
    ];
    $requesterName = trim((string) ($identityRequest->user?->profile?->full_name ?? '')) ?: ($identityRequest->user?->username ?: $identityRequest->user?->email ?: 'User');
    $reviewerName = trim((string) ($identityRequest->reviewer?->profile?->full_name ?? '')) ?: ($identityRequest->reviewer?->username ?: $identityRequest->reviewer?->email ?: 'Not assigned');
@endphp

<div class="block justify-between page-header md:flex">
    <div>
        <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 text-[1.125rem] font-semibold">
            Identity Change Approval
        </h3>
        <p class="text-sm text-[#8c9097] dark:text-white/50 mb-0">
            Compare official identity values against the requested changes before approving or rejecting.
        </p>
    </div>
    <ol class="flex items-center whitespace-nowrap min-w-0">
        <li class="text-[0.813rem] ps-[0.5rem]">
            <a class="flex items-center text-primary hover:text-primary truncate" href="{{ route('identity-change-requests.index') }}">
                Identity Change Requests
                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] px-[0.5rem] overflow-visible rtl:rotate-180"></i>
            </a>
        </li>
        <li class="text-[0.813rem] text-defaulttextcolor font-semibold" aria-current="page">
            Review
        </li>
    </ol>
</div>

<div class="grid grid-cols-12 gap-6">
    @if (session('success'))
        <div class="col-span-12">
            <div class="alert alert-success !mb-0">
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if ($errors->any())
        <div class="col-span-12">
            <div class="alert alert-danger !mb-0">
                <ul class="list-disc list-inside mb-0 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    <div class="xl:col-span-8 col-span-12">
        <div class="box">
            <div class="box-header justify-between items-center sm:flex block">
                <div>
            <div class="box-title">Requested Identity Changes</div>
            <p class="text-xs text-[#8c9097] dark:text-white/50 mt-1 mb-0">
                Submitted by {{ $requesterName }} on {{ optional($identityRequest->created_at)->format('M d, Y h:i A') }} for administrator review.
            </p>
        </div>
                <span class="badge {{ $statusClasses[$identityRequest->status] ?? 'bg-light text-defaulttextcolor' }}">
                    {{ str($identityRequest->status)->replace('_', ' ')->title() }}
                </span>
            </div>
            <div class="box-body">
                <div class="grid grid-cols-12 gap-4 mb-6">
                    <div class="xl:col-span-6 col-span-12">
                        <div class="p-4 rounded-md border border-defaultborder dark:border-defaultborder/10">
                            <p class="text-xs uppercase tracking-wide text-[#8c9097] dark:text-white/50 mb-2">Requester</p>
                            <p class="font-semibold mb-1">{{ $requesterName }}</p>
                            <p class="text-sm text-[#8c9097] dark:text-white/50 mb-1">{{ $identityRequest->user?->email ?: 'No email' }}</p>
                            <p class="text-sm text-[#8c9097] dark:text-white/50 mb-0">
                                {{ $identityRequest->user?->primaryDepartment?->name ?: 'No home department' }}
                            </p>
                        </div>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <div class="p-4 rounded-md border border-defaultborder dark:border-defaultborder/10">
                            <p class="text-xs uppercase tracking-wide text-[#8c9097] dark:text-white/50 mb-2">Review Notes</p>
                            <p class="text-sm mb-1">Reviewed by: {{ $identityRequest->reviewed_at ? $reviewerName : 'Not yet reviewed' }}</p>
                            <p class="text-sm mb-1">Reviewed at: {{ optional($identityRequest->reviewed_at)->format('M d, Y h:i A') ?: 'Pending' }}</p>
                            <p class="text-sm mb-0">
                                {{ $identityRequest->review_notes ?: 'No review notes recorded yet.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table whitespace-nowrap min-w-full">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Official Value At Submission</th>
                                <th>Requested Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($comparisonRows as $row)
                                <tr>
                                    <td class="font-semibold">{{ $row['label'] }}</td>
                                    <td>{{ $row['current'] }}</td>
                                    <td>{{ $row['requested'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 p-4 rounded-md border border-defaultborder dark:border-defaultborder/10 bg-light/30 dark:bg-bodybg/30">
                    <p class="font-semibold mb-2">Submitted Reason</p>
                    <p class="text-sm mb-0">{{ $identityRequest->reason ?: 'No reason was provided.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="xl:col-span-4 col-span-12">
        <div class="box">
            <div class="box-header">
                <div class="box-title">Workflow Actions</div>
            </div>
            <div class="box-body space-y-4">
                @if ($relatedTask)
                    <a href="{{ route('tasks.show', $relatedTask->id) }}" class="ti-btn ti-btn-light btn-wave w-full">
                        Open Administrator Task
                    </a>
                @endif

                @if ($identityRequest->isPending())
                    <form method="POST" action="{{ route('identity-change-requests.approve', $identityRequest->id) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="form-label">Approval Notes</label>
                            <textarea name="review_notes" rows="4" class="form-control w-full !rounded-md" placeholder="Optional review notes for the requester.">{{ old('review_notes') }}</textarea>
                        </div>
                        <button type="submit" class="ti-btn btn-wave bg-success text-white w-full">
                            Approve Identity Change
                        </button>
                    </form>

                    <form method="POST" action="{{ route('identity-change-requests.reject', $identityRequest->id) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="form-label">Rejection Notes</label>
                            <textarea name="review_notes" rows="4" class="form-control w-full !rounded-md" placeholder="Optional review notes for the requester.">{{ old('review_notes') }}</textarea>
                        </div>
                        <button type="submit" class="ti-btn btn-wave bg-danger text-white w-full">
                            Reject Identity Change
                        </button>
                    </form>
                @else
                    <div class="p-4 rounded-md border border-defaultborder dark:border-defaultborder/10 bg-light/30 dark:bg-bodybg/30">
                        <p class="font-semibold mb-1">Approval closed</p>
                        <p class="text-sm mb-0">
                            This identity change request has already been {{ $identityRequest->status }}.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
