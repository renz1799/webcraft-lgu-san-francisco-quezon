<x-print.panel
    kicker="Reports"
    title="Audit Log Print Preview"
    copy="Set the report filters here, review the printable document on the right, then download the PDF."
>
    <div class="core-print-sidebar">
        <div class="core-print-sidebar__intro">
            <div class="core-print-sidebar__eyebrow">Report Controls</div>
            <p class="core-print-sidebar__help">
                Use the filters below to refine the printable audit log report.
            </p>
        </div>

        <form method="GET" action="{{ route('audit-logs.print.index') }}" class="core-print-sidebar__form">
            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Filters</div>

                <div class="core-print-sidebar__field">
                    <label for="date_from" class="form-label">Date From</label>
                    <input
                        type="date"
                        name="date_from"
                        id="date_from"
                        value="{{ $filters['date_from'] ?? '' }}"
                        class="form-control"
                    >
                </div>

                <div class="core-print-sidebar__field">
                    <label for="date_to" class="form-label">Date To</label>
                    <input
                        type="date"
                        name="date_to"
                        id="date_to"
                        value="{{ $filters['date_to'] ?? '' }}"
                        class="form-control"
                    >
                </div>

                <div class="core-print-sidebar__field">
                    <label for="module_name" class="form-label">Module</label>
                    <input
                        type="text"
                        name="module_name"
                        id="module_name"
                        value="{{ $filters['module_name'] ?? '' }}"
                        class="form-control"
                        placeholder="e.g. access"
                    >
                </div>

                <div class="core-print-sidebar__field">
                    <label for="action" class="form-label">Action</label>
                    <input
                        type="text"
                        name="action"
                        id="action"
                        value="{{ $filters['action'] ?? '' }}"
                        class="form-control"
                        placeholder="e.g. user.updated"
                    >
                </div>

                <div class="core-print-sidebar__field">
                    <label for="subject_type" class="form-label">Subject Type</label>
                    <input
                        type="text"
                        name="subject_type"
                        id="subject_type"
                        value="{{ $filters['subject_type'] ?? '' }}"
                        class="form-control"
                        placeholder="Model or type"
                    >
                </div>

                <div class="core-print-sidebar__field">
                    <label for="search" class="form-label">Search</label>
                    <input
                        type="search"
                        name="search"
                        id="search"
                        value="{{ $filters['search'] ?? '' }}"
                        class="form-control"
                        placeholder="Search message or action"
                    >
                </div>
            </div>

            <div class="core-print-sidebar__section core-print-sidebar__section--actions">
                <div class="core-print-sidebar__section-title">Actions</div>

                <div class="core-print-sidebar__actions">
                    <button type="submit" class="ti-btn btn-wave ti-btn-primary-full w-full">
                        Apply Filters
                    </button>

                    <a
                        href="{{ route('audit-logs.print.pdf', $filters) }}"
                        class="ti-btn btn-wave ti-btn-outline-primary label-ti-btn w-full text-center"
                    >
                        <i class="ri-file-pdf-line label-ti-btn-icon me-2"></i>
                        Download PDF
                    </a>
                    <a
                        href="{{ route('audit-logs.print.index') }}"
                        class="core-print-sidebar__reset"
                    >
                        Reset Filters
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-print.panel>
