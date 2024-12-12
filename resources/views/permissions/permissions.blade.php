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
                    <th scope="col" class="text-start">Status</th>
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
            <td class="text-center">
                <!-- Status Toggle Checkbox -->
                <input type="checkbox"
                       class="ti-switch shrink-0 !w-[35px] !h-[21px] before:size-4 toggle-status"
                       data-user-id="{{ $user->id }}"
                       {{ $user->is_active ? 'checked' : '' }}>
            </td>
            <td>
                <div class="hstack flex gap-3 text-[.9375rem]">
                    <a aria-label="anchor" href="javascript:void(0);"
                        class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full"
                        data-hs-overlay="#permissionsModal"
                        data-user-id="{{ $user->id }}">
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
                <button type="button" class="ti-btn btn-wave bg-primary text-white !font-medium" id="submitPermissionsButton">
    Save Changes
</button>

            </div>

        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmationModal" class="hs-overlay hidden ti-modal">
    <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
        <div class="ti-modal-content">
            <!-- Modal Header -->
            <div class="ti-modal-header">
                <h6 class="modal-title text-[1rem] font-semibold">Delete User</h6>
                <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor"
                        data-hs-overlay="#deleteConfirmationModal">
                    <span class="sr-only">Close</span>
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="ti-modal-body px-4 text-center">
                <p class="text-[0.875rem] text-defaulttextcolor">Are you sure you want to delete this user? This action cannot be undone.</p>
            </div>

            <!-- Modal Footer -->
            <div class="ti-modal-footer text-center">
                <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle"
                        data-hs-overlay="#deleteConfirmationModal">
                    Cancel
                </button>
                <button type="button" class="ti-btn btn-wave bg-danger text-white !font-medium"
                        id="confirmDeleteButton">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Utility to show modal
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden'); // Show modal by removing 'hidden'
            modal.classList.add('active'); // Optional: add 'active' class for visual cues
            console.log(`Modal ${modalId} is now visible.`);
        } else {
            console.error(`Modal not found: ${modalId}`);
        }
    }

    // Utility to hide modal
    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden'); // Hide modal by adding 'hidden'
            modal.classList.remove('active'); // Remove 'active' if added
            console.log(`Modal ${modalId} is now hidden.`);
        } else {
            console.error(`Modal not found: ${modalId}`);
        }
    }

    // Attach event listeners to delete buttons
    document.querySelectorAll('.ti-btn-danger').forEach(button => {
        button.addEventListener('click', function () {
            const userId = this.getAttribute('data-user-id');
            if (!userId) {
                console.error('User ID not found on delete button.');
                return;
            }
            console.log(`Delete button clicked for User ID: ${userId}`);

            const confirmDeleteButton = document.getElementById('confirmDeleteButton');
            if (confirmDeleteButton) {
                confirmDeleteButton.dataset.userId = userId; // Attach user ID to confirm button
                showModal('deleteConfirmationModal'); // Show delete confirmation modal
            } else {
                console.error('Confirm Delete Button not found.');
            }
        });
    });

    // Confirm delete button action
    const confirmDeleteButton = document.getElementById('confirmDeleteButton');
    if (confirmDeleteButton) {
        confirmDeleteButton.addEventListener('click', async function () {
            const userId = this.dataset.userId;
            if (!userId) {
                console.error('User ID not found in confirm delete button.');
                return;
            }
            console.log(`Confirm Delete button clicked for User ID: ${userId}`);

            try {
                const response = await fetch(`/users/${userId}/delete`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                });

                if (response.ok) {
                    const result = await response.json();
                  //  alert(result.message); // Success message
                    hideModal('deleteConfirmationModal'); // Hide modal
                    location.reload(); // Refresh page
                } else {
                    const error = await response.json();
                    alert(`Failed to delete user: ${error.message}`);
                }
            } catch (error) {
                console.error('Error deleting user:', error);
                alert('An error occurred while deleting the user. Please try again.');
            }
        });
    } else {
        console.error('Confirm Delete Button not found in DOM.');
    }

    // Close modal on cancel button click
    document.querySelectorAll('[data-hs-overlay="#deleteConfirmationModal"]').forEach(button => {
        button.addEventListener('click', function () {
            hideModal('deleteConfirmationModal');
        });
    });

    // Permissions Modal Logic
    document.querySelectorAll('[data-hs-overlay="#permissionsModal"]').forEach(button => {
        button.addEventListener('click', async function () {
            const userId = this.getAttribute('data-user-id');
            const response = await fetch(`/permissions/${userId}/get`);
            const { permissions, userPermissions } = await response.json();

            const permissionsTable = document.getElementById('permissionsTable');
            permissionsTable.innerHTML = ''; // Clear previous content

            // Populate permissions table dynamically
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

    // Submit Permissions Form

    async function submitPermissionsForm() {
        const form = document.getElementById('permissionsForm');
        const userId = form.dataset.userId; // Get user ID from form
        const formData = new FormData(form);

        const permissions = {};
        for (const [key, value] of formData.entries()) {
            const [page, action] = key.match(/\[([^\]]+)\]\[([^\]]+)\]/).slice(1);
            permissions[page] = permissions[page] || {};
            permissions[page][action] = true;
        }

        const feedbackMessage = document.getElementById('feedbackMessage');
        feedbackMessage.classList.add('hidden'); // Hide previous feedback

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
                feedbackMessage.textContent = 'Permissions updated successfully.';
                feedbackMessage.className = 'text-green-600 text-left text-[0.875rem] font-medium mb-4';
            } else {
                feedbackMessage.textContent = 'You do not have permission to update. Please contact the administrator.';
                feedbackMessage.className = 'text-red-600 text-left text-[0.875rem] font-medium mb-4';
            }
        } catch (error) {
            console.error('Error updating permissions:', error);
            feedbackMessage.textContent = 'An error occurred while updating permissions.';
            feedbackMessage.className = 'text-red-600 text-left text-[0.875rem] font-medium mb-4';
        }

        feedbackMessage.classList.remove('hidden'); // Display feedback
    }

    // Add event listener to the "Save Changes" button
    const submitPermissionsButton = document.getElementById('submitPermissionsButton');
    if (submitPermissionsButton) {
        submitPermissionsButton.addEventListener('click', submitPermissionsForm);
    } else {
        console.error('Submit Permissions Button not found.');
    }
});

</script>

<script>
    
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toggle-status').forEach(toggle => {
        toggle.addEventListener('change', async function () {
            const userId = this.getAttribute('data-user-id');
            const isActive = this.checked;

            try {
                const response = await fetch(`/users/${userId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ is_active: isActive }),
                });

                if (response.ok) {
                    const result = await response.json();
                    console.log(`User status updated: ${result.message}`);
                } else {
                    const error = await response.json();
                    console.error(`Failed to update status: ${error.message}`);
                    alert(`Error: ${error.message}`);
                    this.checked = !isActive; // Revert toggle state on failure
                }
            } catch (error) {
                console.error('Error updating status:', error);
                alert('An error occurred while updating status. Please try again.');
                this.checked = !isActive; // Revert toggle state on failure
            }
        });
    });
});
</script>
@endsection
