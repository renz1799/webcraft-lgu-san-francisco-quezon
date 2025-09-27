@extends('layouts.master')

@section('content')
<div class="block justify-between page-header md:flex">
  <div><h3 class="text-[1.125rem] font-semibold">System Activity</h3></div>
</div>

<div class="box mb-4">
  <div class="box-body">
    <form method="GET" class="grid grid-cols-12 gap-3">
      <input type="text" name="action" value="{{ $filters['action'] ?? '' }}" placeholder="action e.g. user.role.changed" class="form-control xl:col-span-4 col-span-12">
      <input type="text" name="actor_id" value="{{ $filters['actor_id'] ?? '' }}" placeholder="actor uuid" class="form-control xl:col-span-3 col-span-12">
      <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control xl:col-span-2 col-span-6">
      <input type="date" name="date_to"   value="{{ $filters['date_to'] ?? '' }}" class="form-control xl:col-span-2 col-span-6">
      <button class="ti-btn ti-btn-primary-full !rounded-full btn-wave xl:col-span-1 col-span-12">Filter</button>
    </form>
  </div>
</div>

<div class="box">
  <div class="box-header"><h6 class="text-[1rem] font-semibold">Recent Activity</h6></div>
  <div class="box-body">
    <div class="table-responsive">
      <table class="table whitespace-nowrap min-w-full">
        <thead class="bg-primary/10">
        <tr class="border-b border-primary/10">
          <th class="text-start">When</th>
          <th class="text-start">User</th>
          <th class="text-start">Action</th>
          <th class="text-start">Subject</th>
          <th class="text-start">Request</th>
          <th class="text-start">IP</th>
          <th class="text-start">Changes</th>
        </tr>
        </thead>
        <tbody>
@forelse ($logs as $log)
  <tr class="border-b border-primary/10" id="log-{{ $log->id }}">
    <td class="text-start">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>

    {{-- USER (actor) --}}
    <td class="text-start">
      @php $actor = $log->actor; @endphp
      @if ($actor)
        <span class="inline-flex items-center gap-2">
          <span class="font-medium">
            {{ $actor->username ?? $actor->name ?? $actor->email ?? 'User' }}
          </span>
          <button type="button"
                  class="ti-btn ti-btn-xs ti-btn-light !rounded-full"
                  data-action="copy"
                  data-copy="{{ $actor->id }}"
                  title="Copy user UUID">
            <i class="ri-clipboard-line"></i>
          </button>
        </span>
      @else
        <span class="text-muted">—</span>
        @if($log->actor_id)
          <button type="button"
                  class="ti-btn ti-btn-xs ti-btn-light !rounded-full ms-2"
                  data-action="copy"
                  data-copy="{{ $log->actor_id }}"
                  title="Copy user UUID">
            <i class="ri-clipboard-line"></i>
          </button>
        @endif
      @endif
    </td>

    <td class="text-start">{{ $log->action }}</td>

{{-- SUBJECT (what changed) --}}
<td class="text-start">
  @php
    $sub   = $log->subject;                    // may be soft-deleted
    $old   = (array) ($log->changes_old ?? []);
    $new   = (array) ($log->changes_new ?? []);
    $type  = $log->subject_type ? class_basename($log->subject_type) : null;

    // Try to get a human label from resolved model, else from snapshots
    $name =
        ($sub->name ?? null)
        ?? ($old['name'] ?? null)
        ?? ($new['name'] ?? null);

    $page =
        ($sub->page ?? null)
        ?? ($old['page'] ?? null)
        ?? ($new['page'] ?? null);

    // User-specific fallback if name missing
    if ($type === 'User' && ! $name) {
        $name = $sub->username ?? $sub->email ?? ($old['username'] ?? $old['email'] ?? ($new['username'] ?? $new['email'] ?? 'User'));
    }

    $isDeleted = $sub && method_exists($sub, 'trashed') && $sub->trashed();
  @endphp

  <span class="inline-flex items-center gap-2">
    @if ($type)
      <span>
        {{ $type }}
        @if ($name) : {{ $name }} @endif
        @if ($page) — {{ $page }} @endif
        @if ($isDeleted) <span class="text-red-500">(deleted)</span> @endif
        @unless($name) #{{ $log->subject_id }} @endunless
      </span>
    @else
      —
    @endif

    @if ($log->subject_id)
      <button type="button"
              class="ti-btn ti-btn-xs ti-btn-light !rounded-full"
              data-action="copy"
              data-copy="{{ $log->subject_id }}"
              title="Copy subject UUID">
        <i class="ri-clipboard-line"></i>
      </button>
    @endif
  </span>
</td>


    <td class="text-start">{{ $log->request_method }} {{ str($log->request_url)->limit(48) }}</td>
    <td class="text-start">{{ $log->ip }}</td>

    <td class="text-start">
      <button
        type="button"
        class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full"
        data-action="view-log"
        data-message="{{ e($log->message) }}"
        data-old='@json($log->changes_old)'
        data-new='@json($log->changes_new)'
        data-meta='@json($log->meta)'
        data-agent='@json($log->user_agent)'
      >
        <i class="ri-eye-line"></i>
      </button>
    </td>
  </tr>
@empty
  <tr><td colspan="7" class="text-center py-6 text-muted">No activity yet.</td></tr>
@endforelse
</tbody>

      </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
  </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/logs.js')
@endsection
