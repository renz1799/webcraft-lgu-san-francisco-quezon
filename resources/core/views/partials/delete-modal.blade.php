<!-- Global Delete Confirmation Modal -->
<div id="globalDeleteConfirmationModal" class="hs-overlay hidden ti-modal">
    <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out min-h-[calc(100%-3.5rem)] flex items-center">
        <div class="ti-modal-content">
            <!-- Modal Header -->
            <div class="ti-modal-header">
                <h6 class="modal-title text-[1rem] font-semibold">Delete Confirmation</h6>
                <button type="button" class="hs-dropdown-toggle ti-modal-close-btn" 
                        data-hs-overlay="#globalDeleteConfirmationModal">
                    <span class="sr-only">Close</span>
                    <svg class="w-3.5 h-3.5" width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M1 1L7 7M7 1L1 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
</svg>

                </button>
            </div>

            <!-- Modal Body -->
            <div class="ti-modal-body">
                <p id="globalDeleteConfirmationText">Are you sure you want to delete this item?</p>
            </div>

            <!-- Modal Footer -->
            <div class="ti-modal-footer">
                <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full"
                        data-hs-overlay="#globalDeleteConfirmationModal">
                    Cancel
                </button>
                <button type="button" class="ti-btn btn-wave ti-btn-danger-full"
                        onclick="confirmGlobalDeletion()">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
