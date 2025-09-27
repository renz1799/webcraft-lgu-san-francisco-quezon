@extends('layouts.master')

@section('styles')
    {{-- add per-page styles here if needed --}}
@endsection

@section('content')

<!-- Page Header -->
<div class="block justify-between page-header md:flex">
  <div>
    <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">
      Manage Permissions
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
      Manage Permissions
    </li>
  </ol>
</div>
<!-- /Page Header -->

<div class="grid grid-cols-12 gap-6 mb-[3rem]">
  <div class="xl:col-span-12 col-span-12">

    <!-- Add Permission -->
    <div class="box mb-6">
      <div class="box-header">
        <h6 class="text-[1rem] font-semibold">Add New Permission</h6>
      </div>
      <div class="box-body">
        @if (session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
          <div class="alert alert-danger mb-4">
            <ul class="list-disc ms-6">
              @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route('permissions.store') }}" method="POST" class="space-y-4">
          @csrf

          <div class="sm:grid grid-cols-12 gap-6">
            <div class="xl:col-span-6 col-span-12">
              <label for="permission_name" class="form-label">Permission Name</label>
              <input
                type="text"
                id="permission_name"
                name="name"
                value="{{ old('name') }}"
                class="form-control w-full !rounded-md"
                placeholder='e.g., "view Login Logs" or "modify User Lists"'
                required
              >
              @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="xl:col-span-4 col-span-12">
              <label for="page" class="form-label">Page / Module</label>
              <input
                type="text"
                id="page"
                name="page"
                value="{{ old('page') }}"
                class="form-control w-full !rounded-md"
                placeholder='e.g., "Login Logs" or "Manage Users"'
                required
              >
              @error('page') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="xl:col-span-2 col-span-12">
              <label for="guard_name" class="form-label">Guard</label>
              <select id="guard_name" name="guard_name" class="form-control w-full !rounded-md">
                <option value="web" {{ old('guard_name','web') === 'web' ? 'selected' : '' }}>Web</option>
                <option value="api" {{ old('guard_name') === 'api' ? 'selected' : '' }}>API</option>
              </select>
              @error('guard_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
          </div>

          <div>
            <button type="submit" class="ti-btn ti-btn-primary-full !rounded-full btn-wave">
              Add Permission
            </button>
          </div>
        </form>
      </div>
    </div>
    <!-- /Add Permission -->

    <!-- Permissions Table -->
<div class="box">
  <div class="box-header">
    <h6 class="text-[1rem] font-semibold">Existing Permissions</h6>
  </div>
  <div class="box-body">

    @php
      // Support both Collection and Paginator
      $collection = $permissions instanceof \Illuminate\Contracts\Pagination\Paginator
        ? collect($permissions->items())
        : collect($permissions);

      $grouped = $collection
        ->groupBy(fn($p) => $p->page ?: 'Uncategorized')
        ->sortKeys(); // Sort groups by page name
    @endphp

            <div class="table-responsive">
            <table class="table whitespace-nowrap min-w-full">
                <thead class="bg-primary/10">
                <tr class="border-b border-primary/10">
                    <th scope="col" class="text-start">Permission</th>
                    <th scope="col" class="text-start">Guard</th>
                    <th scope="col" class="text-start">Actions</th>
                </tr>
                </thead>
                    <tbody>
                    @forelse ($grouped as $pageName => $items)
                        <tr class="bg-primary/5">
                        <td colspan="3" class="p-3 font-semibold">
                            {{ $pageName }}
                            <span class="text-xs text-muted">({{ $items->count() }})</span>
                        </td>
                        </tr>

                        @foreach ($items->sortBy('name') as $permission)
                        <tr id="perm-row-{{ $permission->id }}" class="border-b border-primary/10">
                            <th scope="row" class="text-start">{{ $permission->name }}</th>
                            <td class="text-start">{{ $permission->guard_name }}</td>
                            <td>
                            <div class="hstack flex gap-3 text-[.9375rem]">
                                <button
                                type="button"
                                aria-label="Delete"
                                class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                                data-action="delete-permission"
                                data-endpoint="{{ route('permissions.destroy', $permission) }}"
                                data-row="perm-row-{{ $permission->id }}"
                                data-name="{{ $permission->name }}"
                                >
                                <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                            </td>
                        </tr>
                        @endforeach
                    @empty
                        <tr>
                        <td colspan="3" class="text-center text-muted py-6">No permissions found.</td>
                        </tr>
                    @endforelse
                    </tbody>
            </table>
            </div>

    {{-- Pagination (if controller used paginate()) --}}
    @if ($permissions instanceof \Illuminate\Contracts\Pagination\Paginator)
      <div class="mt-4">
        {{ $permissions->links() }}
      </div>
    @endif
  </div>
</div>

    <!-- /Permissions Table -->

  </div>
</div>

@endsection

@section('scripts')
  @vite('resources/js/permissions-manage.js')
@endsection

