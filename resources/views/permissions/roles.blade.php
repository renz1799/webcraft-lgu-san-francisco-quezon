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
                            @if ($role->permissions->isEmpty())
                                <span class="text-muted">No permissions assigned</span>
                            @else
                                {{ $role->permissions->pluck('name')->join(', ') }}
                            @endif
                        </td>
                        <td>
                            <div class="hstack flex gap-3 text-[.9375rem]">
                                <!-- Edit Button -->
                                <a aria-label="Edit Role" href="javascript:void(0);"
                                    class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full"
                                    data-hs-overlay="#editRoleModal"
                                    data-role-id="{{ $role->id }}"
                                    data-role-name="{{ $role->name }}"
                                    data-role-permissions="{{ $role->permissions->pluck('id') }}">
                                    <i class="ri-edit-line"></i>
                                </a>

                                <!-- Delete Button -->
                                <a aria-label="Delete Role" href="javascript:void(0);"
                                    class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                                    data-hs-overlay="#deleteRoleModal"
                                    data-role-id="{{ $role->id }}"
                                    data-role-name="{{ $role->name }}">
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
                        data-hs-overlay="#addRoleModal">
                    <i class="ri-close-line"></i>
                </button>
            </div>
            <div class="ti-modal-body px-4">
                <form id="addRoleForm" method="POST" action="{{ route('roles.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="roleName" class="form-label">Role Name</label>
                        <input type="text" id="roleName" name="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               placeholder="Enter role name" required>
                    </div>
                    <div class="mb-4">
                        <h6>Assign Permissions</h6>
                        @foreach ($permissions as $permission)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="permissions[]" 
                                       value="{{ $permission->id }}" id="permission-{{ $permission->id }}">
                                <label class="form-check-label" for="permission-{{ $permission->id }}">
                                    {{ ucfirst($permission->name) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-end">
                        <button type="submit" 
                                class="ti-btn btn-wave bg-primary text-white !font-medium dark:border-defaultborder/10">
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
                        data-hs-overlay="#editRoleModal">
                    <i class="ri-close-line"></i>
                </button>
            </div>
            <div class="ti-modal-body px-4">
                <form id="editRoleForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editRoleId" name="role_id">
                    <div class="mb-4">
                        <label for="editRoleName" class="form-label">Role Name</label>
                        <input type="text" id="editRoleName" name="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               placeholder="Enter role name" required>
                    </div>
                    <div class="mb-4">
                        <h6>Assign Permissions</h6>
                        @foreach ($permissions as $permission)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="permissions[]" 
                                       value="{{ $permission->id }}" id="edit-permission-{{ $permission->id }}">
                                <label class="form-check-label" for="edit-permission-{{ $permission->id }}">
                                    {{ ucfirst($permission->name) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-end">
                        <button type="submit" 
                                class="ti-btn btn-wave bg-primary text-white !font-medium dark:border-defaultborder/10">
                            Update Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Role Modal -->
<div id="deleteRoleModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
    <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
        <div class="ti-modal-content">
            <div class="ti-modal-header">
                <h6 class="modal-title text-[1rem] font-semibold">Delete Role</h6>
                <button type="button" class="hs-dropdown-toggle text-[1rem] font-semibold text-defaulttextcolor"
                        data-hs-overlay="#deleteRoleModal">
                    <i class="ri-close-line"></i>
                </button>
            </div>
            <div class="ti-modal-body px-4">
                <p class="text-center">Are you sure you want to delete the role "<span id="deleteRoleName"></span>"?</p>
            </div>
            <div class="ti-modal-footer text-center">
                <form id="deleteRoleForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" 
                            class="ti-btn btn-wave ti-btn-secondary-full align-middle"
                            data-hs-overlay="#deleteRoleModal">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="ti-btn btn-wave bg-danger text-white !font-medium">
                        Delete Role
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Handle Edit Role Modal
    document.querySelectorAll('[data-hs-overlay="#editRoleModal"]').forEach(button => {
        button.addEventListener('click', function () {
            const roleId = this.getAttribute('data-role-id');
            const roleName = this.getAttribute('data-role-name');
            const rolePermissions = JSON.parse(this.getAttribute('data-role-permissions'));

            document.getElementById('editRoleId').value = roleId;
            document.getElementById('editRoleName').value = roleName;

            // Update permissions
            document.querySelectorAll('#editRoleForm input[name="permissions[]"]').forEach(input => {
                const permissionId = input.value.toString(); // Convert to string
                input.checked = rolePermissions.map(String).includes(permissionId); // Ensure both are strings
            });

            document.getElementById('editRoleForm').action = `/roles/${roleId}`;
        });
    });

    // Handle Delete Role Modal
    document.querySelectorAll('[data-hs-overlay="#deleteRoleModal"]').forEach(button => {
        button.addEventListener('click', function () {
            const roleId = this.getAttribute('data-role-id');
            const roleName = this.getAttribute('data-role-name');

            document.getElementById('deleteRoleName').textContent = roleName;
            document.getElementById('deleteRoleForm').action = `/roles/${roleId}`;
        });
    });
});

</script>
@endsection
