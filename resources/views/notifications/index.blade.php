@extends('layouts.master')

@section('content')
<div id="notifications-index-page" class="block justify-between page-header md:flex">
  <div>
    <h3 class="text-[1.125rem] font-semibold">Notifications</h3>
    <p class="text-sm text-[#8c9097]">Unread: <span class="font-semibold">{{ $unreadCount }}</span></p>
  </div>

  <div class="flex gap-2 mt-2 md:mt-0">
    <a href="{{ route('notifications.index', ['filter' => 'all']) }}"
       class="ti-btn ti-btn-light {{ $filter === 'all' ? 'ti-btn-primary' : '' }}">
      All
    </a>

    <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
       class="ti-btn ti-btn-light {{ $filter === 'unread' ? 'ti-btn-primary' : '' }}">
      Unread
    </a>

    <form method="POST" action="{{ route('notifications.readAll') }}">
      @csrf
      <button type="submit" class="ti-btn ti-btn-secondary">Mark all as read</button>
    </form>
  </div>
</div>

<div class="box">
  <div class="box-header">
    <h6 class="text-[1rem] font-semibold">Recent</h6>
  </div>

  <div class="box-body">
    <div class="table-responsive">
      <table class="table whitespace-nowrap min-w-full">
        <thead class="bg-primary/10">
          <tr class="border-b border-primary/10">
            <th class="text-start">Status</th>
            <th class="text-start">Title</th>
            <th class="text-start">Message</th>
            <th class="text-start">When</th>
            <th class="text-start">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($notifications as $n)
            @php
              $url = data_get($n->data, 'url', '#');
              $isUnread = is_null($n->read_at);
            @endphp
            <tr class="border-b border-primary/10 {{ $isUnread ? 'bg-secondary/5' : '' }}">
              <td class="text-start">
                <span class="text-xs px-2 py-1 rounded {{ $isUnread ? 'bg-secondary/10 text-secondary' : 'bg-gray-100 text-gray-600' }}">
                  {{ $isUnread ? 'Unread' : 'Read' }}
                </span>
              </td>
              <td class="text-start font-medium">{{ $n->title }}</td>
              <td class="text-start text-[#8c9097]">{{ $n->message }}</td>
              <td class="text-start">{{ $n->created_at->format('Y-m-d H:i') }}</td>
              <td class="text-start">
                <a href="{{ $url }}"
                   class="ti-btn ti-btn-sm ti-btn-primary !rounded-full js-open-notif"
                   data-id="{{ $n->id }}">
                  Open
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-6 text-muted">No notifications.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $notifications->links() }}
    </div>
  </div>
</div>
@endsection


