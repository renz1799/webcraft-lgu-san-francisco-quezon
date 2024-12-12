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
    <div class="table-responsive">
        <table class="table whitespace-nowrap min-w-full">
            <thead class="bg-primary/10">
                <tr class="border-b border-primary/10">
                    <th scope="col" class="text-start">User Name</th>
                    <th scope="col" class="text-start">Email</th>
                    <th scope="col" class="text-start">User Type</th>
                    <th scope="col" class="text-start">Created</th>
                    <th scope="col" class="text-start">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-b border-primary/10">
                        <th scope="row" class="text-start">{{ $user->username }}</th>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->user_type }}</td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="hstack flex gap-3 text-[.9375rem]">
                                <a aria-label="anchor" href="javascript:void(0);"
                                    class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full"
                                    data-hs-overlay="#permissionsModal"
                                    data-user-id="{{ $user->id }}">
                                    <i class="ri-edit-line"></i>
                                </a>
                                <a aria-label="anchor" href="javascript:void(0);"
                                class="ti-btn btn-wave ti-btn-sm ti-btn-success !rounded-full"><i
                                    class="ri-contract-up-down-fill"></i></a>
                                    <a aria-label="anchor" href="javascript:void(0);"
                                class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"><i
                                    class="ri-delete-bin-line"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Permissions Modal -->
<div id="permissionsModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
    <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
        <div class="ti-modal-content">
            <!-- Modal Header -->
            <div class="ti-modal-header">
                <h6 class="modal-title text-[1rem] font-semibold">Edit Permissions</h6>
                <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" 
                        data-hs-overlay="#permissionsModal">
                    <span class="sr-only">Close</span>
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="ti-modal-body px-4">
                <form id="permissionsForm">
                    <div class="table-responsive">
                        <table class="table whitespace-nowrap min-w-full border border-primary/10">
                            <!-- Table Header -->
                            <thead class="bg-primary/10">
                                <tr>
                                    <th class="text-start p-3">Page</th>
                                    <th class="text-center p-3">View</th>
                                    <th class="text-center p-3">Modify</th>
                                    <th class="text-center p-3">Delete</th>
                                </tr>
                            </thead>
                            <tbody id="permissionsTable">
                                <!-- Permissions will be dynamically loaded here -->
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="ti-modal-footer">
                <!-- Feedback Message -->
                <div id="feedbackMessage" class="hidden text-center text-[0.875rem] font-medium mb-4"></div>

                <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" 
                        data-hs-overlay="#permissionsModal">
                    Close
                </button>
                <button type="button" class="ti-btn btn-wave bg-primary text-white !font-medium" onclick="submitPermissionsForm()">
                    Save Changes
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.querySelectorAll('[data-hs-overlay="#permissionsModal"]').forEach(button => {
    button.addEventListener('click', async function () {
        const userId = this.getAttribute('data-user-id');
        const response = await fetch(`/permissions/${userId}/get`);
        const { permissions, userPermissions } = await response.json();

        const permissionsTable = document.getElementById('permissionsTable');
        permissionsTable.innerHTML = ''; // Clear previous content

        // Render the table rows dynamically
        Object.entries(permissions).forEach(([page, actions]) => {
            let row = `<tr>
                <td class="p-3 text-start">${page}</td>`;
            actions.forEach(permission => {
                const isChecked = userPermissions.includes(permission.name) ? 'checked' : '';
                row += `
                    <td class="text-center">
                        <input type="checkbox" 
                               name="permissions[${page}][${permission.name.split(' ')[0]}]"
                               value="${permission.name}"
                               class="ti-switch shrink-0 !w-[35px] !h-[21px] before:size-4"
                               ${isChecked}>
                    </td>`;
            });
            row += '</tr>';
            permissionsTable.innerHTML += row;
        });

        // Store user ID in the form for submission
        document.getElementById('permissionsForm').dataset.userId = userId;
    });
});

async function submitPermissionsForm() {
    const form = document.getElementById('permissionsForm');
    const userId = form.dataset.userId; // Get the user ID
    const formData = new FormData(form);

    const permissions = {};
    for (const [key, value] of formData.entries()) {
        const [page, action] = key.match(/\[([^\]]+)\]\[([^\]]+)\]/).slice(1);
        permissions[page] = permissions[page] || {};
        permissions[page][action] = true;
    }

    const feedbackMessage = document.getElementById('feedbackMessage');
    feedbackMessage.classList.add('hidden'); // Hide any previous feedback

    try {
        const response = await fetch(`/permissions/${userId}/update`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ permissions }),
        });

        if (response.ok) {
            // Show success feedback
            feedbackMessage.textContent = 'Permissions updated successfully.';
            feedbackMessage.className = 'text-green-600 text-left text-[0.875rem] font-medium mb-4';
        } else {
            // Show error feedback
            feedbackMessage.textContent = 'You dont have permission to update. Please contact the administrator.';
            feedbackMessage.className = 'text-red-600 text-left text-[0.875rem] font-medium mb-4';
        }
    } catch (error) {
        console.error('Error updating permissions:', error);

        // Show error feedback
        feedbackMessage.textContent = 'An error occurred while updating permissions.';
        feedbackMessage.className = 'text-red-600 text-left text-[0.875rem] font-medium mb-4';
    }

    feedbackMessage.classList.remove('hidden'); // Display the feedback message
}

</script>
@endsection
