@extends('layouts.master')

@section('styles')
        <!-- Tom Select Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/tom-select/css/tom-select.default.min.css')}}">
      
@endsection

@section('content')
 
                  <div class="container">

                    <!-- Page Header -->
                    <div class="block justify-between page-header md:flex">
                        <div>
                            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">Mail Settings</h3>
                        </div>
                        <ol class="flex items-center whitespace-nowrap min-w-0">
                            <li class="text-[0.813rem] ps-[0.5rem]">
                              <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                Mail
                                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                              </a>
                            </li>
                            <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                               Mail Settings
                            </li>
                        </ol>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start::row-1 -->
                    <div class="grid grid-cols-12 gap-6 mb-[3rem]">
                        <div class="xl:col-span-12 col-span-12">
                            <div class="box">
                                <div class="box-header sm:flex block !justify-start">
                                    <nav aria-label="Tabs" class="md:flex block !justify-start whitespace-nowrap" role="tablist">
                                        <a class="m-1 block w-full hs-tab-active:bg-primary/10 hs-tab-active:text-primary cursor-pointer text-defaulttextcolor dark:text-defaulttextcolor/70 py-2 px-3 flex-grow  text-[0.75rem] font-medium rounded-md hover:text-primary active" id="Roles-permission" data-hs-tab="#roles-permission" aria-controls="personal-info">
                                            Roles and Permissions
                                        </a>
                                        <a class="m-1 block w-full hs-tab-active:bg-primary/10 hs-tab-active:text-primary cursor-pointer text-defaulttextcolor dark:text-defaulttextcolor/70 py-2 px-3 text-[0.75rem] flex-grow font-medium rounded-md hover:text-primary " id="account-item" data-hs-tab="#account-settings" aria-controls="account-settings">
                                            Account Settings
                                        </a>
                                    </nav>
                                </div>
                                <div class="box-body">
                                <div class="tab-content">
    <div class="tab-pane show active dark:border-defaultborder/10" id="roles-permission" aria-labelledby="Roles-permission">
        <div class="sm:p-4 p-0">
            
            <!-- Section: Assign Roles to User -->
            <h6 class="font-semibold mb-4 text-[1rem]">Assign Role</h6>
            <form method="POST" action="{{ route('permissions.update', $user->id) }}">
                @csrf
                @method('PUT')
                
                <div class="sm:grid grid-cols-12 gap-6 mb-6">
                    <div class="xl:col-span-6 col-span-12">
                        <label for="user-name" class="form-label">User Name</label>
                        <input type="text" class="form-control w-full !rounded-md" id="user-name" value="{{ $user->username }}" disabled>
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <label for="role" class="form-label">Select Role</label>
                        <select name="role" id="role" class="form-control">
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" {{ $user->role == $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

<!-- Section: Set Permissions -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
    @foreach ($permissions as $page => $actions)
        <div class="mb-6 flex flex-col h-full">
            <h6 class="font-semibold mb-3 text-[1.1rem] flex justify-between items-center">
                {{ $page }}
                <span id="feedback-{{ Str::slug($page) }}" class="text-green-500 text-sm hidden">
                    ✔ Saved
                </span>
            </h6>

            <div class="table-responsive h-full">
                <table class="table whitespace-nowrap border border-primary/10 w-full">
                    <thead class="bg-primary/10">
                        <tr>
                            <th class="p-3 text-start">Permission</th>
                            <th class="p-3 text-center">View</th>
                            <th class="p-3 text-center">Modify</th>
                            <th class="p-3 text-center">Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $groupedPermissions = []; @endphp

                        @foreach ($actions as $permission)
                            @php
                                $words = explode(' ', $permission->name);
                                $action = strtolower(array_shift($words));
                                $cleanName = implode(' ', $words);

                                if (!isset($groupedPermissions[$cleanName])) {
                                    $groupedPermissions[$cleanName] = ['view' => false, 'modify' => false, 'delete' => false];
                                }
                                $groupedPermissions[$cleanName][$action] = $permission->id;
                            @endphp
                        @endforeach

                        @foreach ($groupedPermissions as $permissionName => $actions)
                            <tr>
                                <td class="p-3 text-start">{{ $permissionName }}</td>

                                <td class="text-center">
                                    @if ($actions['view'])
                                        <input type="checkbox" class="permission-checkbox"
                                            name="permissions[{{ $page }}][{{ $permissionName }}][]" 
                                            value="view"
                                            data-page="{{ $page }}" 
                                            data-action="view" 
                                            data-permission="{{ $permissionName }}"
                                            {{ isset($userPermissions[$page][$permissionName]) && in_array('view', $userPermissions[$page][$permissionName]) ? 'checked' : '' }}>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if ($actions['modify'])
                                        <input type="checkbox" class="permission-checkbox"
                                            name="permissions[{{ $page }}][{{ $permissionName }}][]" 
                                            value="modify"
                                            data-page="{{ $page }}" 
                                            data-action="modify" 
                                            data-permission="{{ $permissionName }}"
                                            {{ isset($userPermissions[$page][$permissionName]) && in_array('modify', $userPermissions[$page][$permissionName]) ? 'checked' : '' }}>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if ($actions['delete'])
                                        <input type="checkbox" class="permission-checkbox"
                                            name="permissions[{{ $page }}][{{ $permissionName }}][]" 
                                            value="delete"
                                            data-page="{{ $page }}" 
                                            data-action="delete" 
                                            data-permission="{{ $permissionName }}"
                                            {{ isset($userPermissions[$page][$permissionName]) && in_array('delete', $userPermissions[$page][$permissionName]) ? 'checked' : '' }}>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>

<!-- Save Button -->
<div class="mt-4">
    <button type="button" id="savePermissionsButton" class="ti-btn btn-wave bg-primary text-white m-1">
        Save Changes
    </button>
</div>




            </form>
        </div>
    </div>
</div>

                                </div>
                                <div class="box-footer">
                                    <div class="ltr:float-right rtl:float-left">
                                        <button type="button" class="ti-btn btn-wave ti-btn-light m-1">
                                            Restore Defaults
                                        </button>
                                        <button type="button" class="ti-btn btn-wave bg-primary text-white m-1">
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End::row-1 -->

                  </div>
      
@endsection

@section('scripts')

        <!-- Mail Settings -->
        @vite('resources/assets/js/mail-settings.js')

            <!-- Custom Script for Handling Permissions -->
    <script>
document.addEventListener("DOMContentLoaded", function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');

    if (!csrfToken) {
        console.error("CSRF token meta tag is missing.");
        return;
    }

    const saveButton = document.getElementById('savePermissionsButton');
    if (!saveButton) {
        console.error("savePermissionsButton not found. Ensure the button exists before initializing.");
        return;
    }

    saveButton.addEventListener('click', async function () {
        let selectedPermissions = {};

        document.querySelectorAll('.permission-checkbox:checked').forEach(checkbox => {
            const page = checkbox.getAttribute('data-page');
            const action = checkbox.getAttribute('data-action');
            const permission = checkbox.getAttribute('data-permission');

            if (!selectedPermissions[page]) {
                selectedPermissions[page] = {};
            }
            if (!selectedPermissions[page][permission]) {
                selectedPermissions[page][permission] = [];
            }
            selectedPermissions[page][permission].push(action);
        });

        console.log("Submitting permissions:", JSON.stringify(selectedPermissions));

        try {
            const response = await fetch("{{ route('permissions.update', $user->id) }}", {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken.content
                },
                body: JSON.stringify({ permissions: selectedPermissions })
            });

            if (!response.ok) {
                throw new Error(`Server error: ${response.status}`);
            }

            const result = await response.json();
            console.log("Permissions updated successfully!");

            // Show feedback for each updated section
            Object.keys(selectedPermissions).forEach(page => {
                let feedbackSpan = document.getElementById(`feedback-${page.replace(/\s+/g, '-').toLowerCase()}`);
                if (feedbackSpan) {
                    feedbackSpan.classList.remove("hidden");
                    feedbackSpan.textContent = "✔ Saved";

                    // Hide after 3 seconds
                    setTimeout(() => {
                        feedbackSpan.classList.add("hidden");
                    }, 3000);
                }
            });

        } catch (error) {
            console.error("Error updating permissions:", error);
        }
    });
});


    </script>
   
@endsection 