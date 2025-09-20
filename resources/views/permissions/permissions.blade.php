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
                    <a aria-label="anchor" href="{{ url('/set-user-role-permissions/' . $user->id) }}"
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
});
</script>


<script>
    
document.addEventListener('DOMContentLoaded', () => {
document.querySelectorAll('.toggle-status').forEach(toggle => {
  toggle.addEventListener('change', async function () {
    const endpoint = this.dataset.endpoint;           // from route()
    const isActive = this.checked;

    try {
      const res = await fetch(endpoint, {
        method: 'PATCH',                              // matches route
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ is_active: isActive }),
      });

      if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        this.checked = !isActive;                     // revert on failure
        console.error('Failed to update status:', err.message || res.statusText);
        alert(err.message || 'Failed to update status');
      }
    } catch (e) {
      this.checked = !isActive;
      console.error('Error updating status:', e);
      alert('An error occurred while updating status.');
    }
  });
});

});
</script>
@endsection
