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
        // try to resolve the subject (will work if AuditLog::subject()->withTrashed())
        $sub  = $log->subject;                       // may be null
        $old  = (array) ($log->changes_old ?? []);
        $new  = (array) ($log->changes_new ?? []);
        $type = $log->subject_type ? class_basename($log->subject_type) : null;

        // human label pieces
        $name = $sub->name
            ?? ($old['name'] ?? null)
            ?? ($new['name'] ?? null);

        $page = $sub->page
            ?? ($old['page'] ?? null)
            ?? ($new['page'] ?? null);

        // User-specific fallbacks if 'name' is missing
        if ($type === 'User' && ! $name) {
            $name = $sub->username
                ?? $sub->email
                ?? ($old['username'] ?? $old['email'] ?? ($new['username'] ?? $new['email'] ?? 'User'));
        }

        // final label
        $label = $type
            ? trim($type . ($name ? ' : '.$name : '') . ($page ? ' — '.$page : ''))
            : '—';

        // restore button logic
        $isTrashed          = $sub && method_exists($sub, 'trashed') && $sub->trashed();
        $maybeDeletedAction = str_ends_with($log->action ?? '', '.deleted'); // PHP 8 helper
        $showRestore        = ($log->subject_type && $log->subject_id) && ($isTrashed || $maybeDeletedAction);
      @endphp

      <span class="inline-flex items-center gap-2">
        <span>
          {{ $label }}
          @if ($isTrashed)
            <span class="text-red-500">(deleted)</span>
          @endif
          @if ($type && ! $name && $log->subject_id)
            #{{ $log->subject_id }}
          @endif
        </span>

        @if ($log->subject_id)
          <button type="button"
                  class="ti-btn ti-btn-xs ti-btn-light !rounded-full"
                  data-action="copy"
                  data-copy="{{ $log->subject_id }}"
                  title="Copy subject UUID">
            <i class="ri-clipboard-line"></i>
          </button>
        @endif

      @php
        $typeShort = match($log->subject_type) {
            \App\Models\User::class       => 'user',
            \App\Models\Permission::class => 'permission',
            \App\Models\Role::class       => 'role',
            default => null,
        };
      @endphp

      @if ($showRestore && $typeShort)
        <button type="button"
                class="ti-btn ti-btn-xs ti-btn-warning !rounded-full"
                data-action="restore-subject"
                data-endpoint="{{ route('audit.restore') }}"
                data-type="{{ $typeShort }}"
                data-id="{{ $log->subject_id }}"
                title="Restore this {{ class_basename($log->subject_type) }}">
          <i class="ri-history-line"></i>
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


@push('scripts')
@vite('resources/js/logs.js')
@endpush
@endsection