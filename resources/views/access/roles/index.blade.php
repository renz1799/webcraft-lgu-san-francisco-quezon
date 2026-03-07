@extends('layouts.master')

@section('styles')
    <!-- Add custom styles if needed -->
@endsection

@section('content')

<!-- Page Header -->
<div class="block justify-between page-header md:flex">
    <div>
        <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">
            Roles Management
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
            Roles
        </li>
    </ol>
</div>
<!-- Page Header Close -->

<!-- Roles Table -->
<div class="container mt-5">
    <div class="text-end mb-4">
        <button type="button" class="ti-btn ti-btn-lg bg-primary btn-wave text-white !font-medium dark:border-defaultborder/10"
                data-hs-overlay="#addRoleModal">
            Add New Role
        </button>
    </div>
    <div class="table-responsive">
        <table class="table whitespace-nowrap min-w-full">
            <thead class="bg-primary/10">
                <tr class="border-b border-primary/10">
                    <th scope="col" class="text-start">Role Name</th>
                    <th scope="col" class="text-start">Permissions</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr class="border-b border-primary/10">
                        <th scope="row" class="text-start">{{ $role->name }}</th>
<td>
  @php
    $byPage   = $role->permissions->groupBy(fn($p) => $p->page ?: 'Uncategorized');
    $chunks   = $byPage->map(fn($c,$page) => $page.' ('.$c->count().')')->values();
    $preview  = $chunks->take(2)->join(', ');
    $moreCnt  = max(0, $chunks->count() - 2);
    $permList = $role->permissions
      ->map(fn($p) => ['page' => $p->page ?: 'Uncategorized', 'name' => $p->name])
      ->values();
  @endphp

  @if ($chunks->isEmpty())
    <span class="text-muted">No permissions assigned</span>
  @else
    <span>{{ $preview }}</span>
    @if($moreCnt > 0)
      <span class="text-muted"> +{{ $moreCnt }} more</span>
    @endif
  @endif

  <button type="button"
          class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full ms-2"
          title="View permissions"
          data-action="view-role-perms"
          data-role="{{ $role->name }}"
          data-perms='@json($permList)'>
    <i class="ri-eye-line"></i>
  </button>
</td>

                        <td>
                            <div class="hstack flex gap-3 text-[.9375rem]">
                                <!-- Edit Button -->
                                <a aria-label="Edit Role" href="javascript:void(0);"
                                    class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full"
                                    data-hs-overlay="#editRoleModal"
                                    data-role-id="{{ $role->id }}"
                                    data-role-name="{{ $role->name }}"
                                    data-role-permissions='@json($role->permissions->pluck("id"))'>
                                    <i class="ri-edit-line"></i>
                                </a>

                                <!-- Delete Button -->
                                <a aria-label="Delete Role" href="javascript:void(0);"
                                class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                                data-action="delete-role"
                                data-endpoint="{{ route('access.roles.destroy',$role) }}"
                                data-name="{{ $role->name }}">
                                <i class="ri-delete-bin-line"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">No roles found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Role Modal -->
<div id="addRoleModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
  <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
    <div class="ti-modal-content">
      <div class="ti-modal-header">
        <h6 class="modal-title text-[1rem] font-semibold">Add New Role</h6>
        <button type="button" class="hs-dropdown-toggle text-[1rem] font-semibold text-defaulttextcolor"
                data-hs-overlay="#addRoleModal"><i class="ri-close-line"></i></button>
      </div>

      <div class="ti-modal-body px-4">
        <form id="addRoleForm" method="POST" action="{{ route('access.roles.store') }}">
          @csrf

          <div class="mb-4">
            <label for="roleName" class="form-label">Role Name</label>
            <input type="text" id="roleName" name="name" class="form-control" placeholder="Enter role name" required>
          </div>

          @php
            $permByPage = $permissions->groupBy(fn($p) => $p->page ?: 'Uncategorized')->sortKeys();
          @endphp

          <div class="mb-3 flex items-center justify-between gap-3">
            <h6 class="font-semibold">Assign Permissions</h6>
            <div class="flex gap-2">
              <button type="button" class="ti-btn ti-btn-xs ti-btn-light"
                      data-bulk="check-all" data-scope="add">Check all</button>
              <button type="button" class="ti-btn ti-btn-xs ti-btn-light"
                      data-bulk="uncheck-all" data-scope="add">Uncheck all</button>
            </div>
          </div>

          <div class="space-y-3">
            @foreach ($permByPage as $pageName => $items)
              @php
                $groupKey = Str::slug($pageName);
              @endphp
              <div class="border rounded-lg p-3">
                <div class="flex items-center justify-between">
                  <div class="font-medium">
                    {{ $pageName }} <span class="text-xs text-muted">({{ $items->count() }})</span>
                  </div>
                </div>

                <div id="add-group-{{ $groupKey }}"
                     class="grid md:grid-cols-2 gap-x-6 gap-y-2 mt-3">
                  @foreach ($items->sortBy('name') as $p)
                    <label class="inline-flex items-center gap-2">
                      <input type="checkbox" class="form-check-input"
                             name="permissions[]" value="{{ $p->id }}">
                      <span>{{ $p->name }}</span>
                    </label>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>

          <div class="text-end mt-4">
            <button type="submit" class="ti-btn btn-wave bg-primary text-white !font-medium">
              Save Role
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Role Modal -->
<div id="editRoleModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
  <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
    <div class="ti-modal-content">
      <div class="ti-modal-header">
        <h6 class="modal-title text-[1rem] font-semibold">Edit Role</h6>
        <button type="button" class="hs-dropdown-toggle text-[1rem] font-semibold text-defaulttextcolor"
                data-hs-overlay="#editRoleModal"><i class="ri-close-line"></i></button>
      </div>

      <div class="ti-modal-body px-4">
        <form id="editRoleForm" method="POST">
          @csrf
          @method('PUT')
          <input type="hidden" id="editRoleId" name="role_id">

          <div class="mb-4">
            <label for="editRoleName" class="form-label">Role Name</label>
            <input type="text" id="editRoleName" name="name" class="form-control" required>
          </div>

          <div class="mb-3 flex items-center justify-between gap-3">
            <h6 class="font-semibold">Assign Permissions</h6>
            <div class="flex gap-2">
              <button type="button" class="ti-btn ti-btn-xs ti-btn-light"
                      data-bulk="check-all" data-scope="edit">Check all</button>
              <button type="button" class="ti-btn ti-btn-xs ti-btn-light"
                      data-bulk="uncheck-all" data-scope="edit">Uncheck all</button>
            </div>
          </div>

          <div class="space-y-3">
            @foreach ($permByPage as $pageName => $items)
              @php
                $groupKey = Str::slug($pageName);
              @endphp
              <div class="border rounded-lg p-3">
                <div class="flex items-center justify-between">
                  <div class="font-medium">
                    {{ $pageName }} <span class="text-xs text-muted">({{ $items->count() }})</span>
                  </div>
                </div>

                <div id="edit-group-{{ $groupKey }}"
                     class="grid md:grid-cols-2 gap-x-6 gap-y-2 mt-3">
                  @foreach ($items->sortBy('name') as $p)
                    <label class="inline-flex items-center gap-2">
                      <input type="checkbox" class="form-check-input"
                             name="permissions[]" value="{{ $p->id }}">
                      <span>{{ $p->name }}</span>
                    </label>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>

          <div class="text-end mt-4">
            <button type="submit" class="ti-btn btn-wave bg-primary text-white !font-medium">
              Update Role
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>



@push('scripts')
@vite(['resources/js/roles-page.js'])
@endpush
@endsection