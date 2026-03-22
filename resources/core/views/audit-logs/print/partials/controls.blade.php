@php
    $adminRoutes = $adminRoutes ?? app(\App\Core\Support\AdminRouteResolver::class);
    $selectedPaper = $filters['paper_profile'] ?? config('print.modules.audit_logs.default_paper', 'a4-portrait');

    $allowedPapers = config('print.modules.audit_logs.allowed_papers', []);

    $paperOptions = collect($allowedPapers)
        ->mapWithKeys(fn ($code) => [
            $code => config("print.papers.{$code}.label", $code),
        ])
        ->all();

    $subjectTypeOptions = [
        '' => 'All subjects',
        'user' => 'User',
        'permission' => 'Permission',
        'role' => 'Role',
    ];

    $pdfParams = array_filter([
        'date_from' => $filters['date_from'] ?? null,
        'date_to' => $filters['date_to'] ?? null,
        'module' => $filters['module'] ?? null,
        'action' => $filters['action'] ?? null,
        'actor_id' => $filters['actor_id'] ?? null,
        'subject_type' => $filters['subject_type'] ?? null,
        'search' => $filters['search'] ?? null,
        'paper_profile' => $selectedPaper,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

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

        <form method="GET" action="{{ $adminRoutes->route('audit-logs.print.index') }}" class="core-print-sidebar__form">
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
                    <label for="module" class="form-label">Module</label>
                    @if ($adminRoutes->isModuleScoped())
                        <input
                            type="text"
                            name="module"
                            id="module"
                            value="{{ strtoupper((string) $adminRoutes->scopedModuleCode()) }}"
                            class="form-control"
                            readonly
                        >
                    @else
                        <input
                            type="text"
                            name="module"
                            id="module"
                            value="{{ $filters['module'] ?? '' }}"
                            class="form-control"
                            placeholder="Code or name"
                        >
                    @endif
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
                    <label for="actor_id" class="form-label">Actor ID</label>
                    <input
                        type="text"
                        name="actor_id"
                        id="actor_id"
                        value="{{ $filters['actor_id'] ?? '' }}"
                        class="form-control"
                        placeholder="UUID"
                    >
                </div>

                <div class="core-print-sidebar__field">
                    <label for="subject_type" class="form-label">Subject Type</label>
                    <select
                        name="subject_type"
                        id="subject_type"
                        class="form-control"
                    >
                        @foreach ($subjectTypeOptions as $code => $label)
                            <option value="{{ $code }}" @selected(($filters['subject_type'] ?? '') === $code)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
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

                <div class="core-print-sidebar__field">
                    <label for="paper_profile" class="form-label">Paper Size</label>
                    <select
                        name="paper_profile"
                        id="paper_profile"
                        class="form-control"
                    >
                        @foreach ($paperOptions as $code => $label)
                            <option value="{{ $code }}" @selected($selectedPaper === $code)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="core-print-sidebar__section core-print-sidebar__section--actions">
                <div class="core-print-sidebar__section-title">Actions</div>

                <div class="core-print-sidebar__actions">
                    <button type="submit" class="ti-btn btn-wave ti-btn-primary-full w-full">
                        Apply Filters
                    </button>

                    <a
                        href="{{ $adminRoutes->route('audit-logs.print.pdf', $pdfParams) }}"
                        class="ti-btn btn-wave ti-btn-outline-primary label-ti-btn w-full text-center"
                    >
                        <i class="ri-file-pdf-line label-ti-btn-icon me-2"></i>
                        Download PDF
                    </a>

                    <a
                        href="{{ $adminRoutes->route('audit-logs.print.index') }}"
                        class="core-print-sidebar__reset"
                    >
                        Reset Filters
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-print.panel>
