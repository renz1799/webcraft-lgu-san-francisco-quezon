@extends('layouts.master')

@section('styles')
    <!-- Add custom styles here if needed -->
@endsection

@section('content')

<!-- Page Header -->
<div class="block justify-between page-header md:flex">
    <div>
        <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">
            Permissions Management
        </h3>
    </div>
    <ol class="flex items-center whitespace-nowrap min-w-0">
        <li class="text-[0.813rem] ps-[0.5rem]">
            <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                Pages
                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
            </a>
        </li>
        <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50" aria-current="page">
            Permissions
        </li>
    </ol>
</div>
<!-- Page Header Close -->

<!-- Permissions Table -->
<div class="container mt-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <form method="GET" action="{{ route('users.permissions.index') }}" class="flex gap-2 w-full sm:w-auto">
        <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Search by email or username..."
            class="form-control w-full sm:w-[320px] !rounded-md"
        />
        <button type="submit" class="ti-btn btn-wave bg-primary text-white">
            <i class="ri-search-line align-middle"></i> Search
        </button>

        @if(request()->filled('q'))
            <a href="{{ route('users.permissions.index') }}" class="ti-btn btn-wave ti-btn-light">
                Clear
            </a>
        @endif
    </form>

    @if(isset($users) && method_exists($users, 'total'))
        <div class="text-[0.75rem] text-[#8c9097] dark:text-white/50">
            Showing {{ $users->count() }} of {{ $users->total() }} users
        </div>
    @endif
</div>

    <div class="table-responsive">
        <table class="table whitespace-nowrap min-w-full">
            <thead class="bg-primary/10">
                <tr class="border-b border-primary/10">
                    <th scope="col" class="text-start">User Name</th>
                    <th scope="col" class="text-start">Email</th>
                    <th scope="col" class="text-start">Role</th>
                    <th scope="col" class="text-start">Created</th>
                    <th scope="col" class="text-start">Status</th>
                    <th scope="col" class="text-start">Actions</th>
                </tr>
            </thead>
            <tbody>
     @forelse ($users as $user)
        <tr class="border-b border-primary/10">
            <th scope="row" class="text-start">{{ $user->username }}</th>
            <td>{{ $user->email }}</td>
            <td>
                {{ optional($user->roles->first())->name ?? 'No Role Assigned' }} <!-- Fetching User Role -->
            </td>
            <td>{{ $user->created_at->format('d M Y') }}</td>
            <td class="text-center">
                <!-- Status Toggle Checkbox -->
                <input
                type="checkbox"
                class="ti-switch shrink-0 !w-[35px] !h-[21px] before:size-4 toggle-status"
                data-endpoint="{{ route('users.status.update', $user) }}"
                {{ $user->is_active ? 'checked' : '' }}>
            </td>
            <td>
                <div class="hstack flex gap-3 text-[.9375rem]">
                    <a aria-label="anchor"  href="{{ route('users.permissions.edit', $user) }}"
                        class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full">
                        <i class="ri-edit-line"></i>
                    </a>
                    <a href="javascript:void(0);"
                        class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                        data-action="delete-user"
                        data-user-id="{{ $user->id }}">
                        <i class="ri-delete-bin-line"></i>
                    </a>
                </div>
            </td>
        </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted">No users found.</td>
            </tr>
        @endforelse
     </tbody>

            </table>
            @if(isset($users) && method_exists($users, 'links'))
    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endif

    </div>
</div>

@push('scripts')
  @vite('resources/js/permissions.js')
@endpush
@endsection