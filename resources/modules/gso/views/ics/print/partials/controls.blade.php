@php
    $printConfig = app(\App\Core\Services\Contracts\Print\PrintConfigLoaderInterface::class);
    $printable = $printConfig->printable('gso_ics');
    $ics = $report['ics'] ?? [];
    $document = $report['document'] ?? [];
    $summary = $document['summary'] ?? [];
    $pagination = $report['pagination']['stats'] ?? [];
    $selectedPaper = $filters['paper_profile'] ?? ($paperProfile['code'] ?? $printConfig->defaultPaper('gso_ics', 'a4-portrait'));
    $allowedPapers = $printConfig->allowedPapers('gso_ics', 'a4-portrait');
    $paperOptions = collect($allowedPapers)
        ->mapWithKeys(fn ($code) => [$code => config("print.papers.{$code}.label", $code)])
        ->all();
    $paperDefaults = collect($allowedPapers)
        ->mapWithKeys(function (string $code) use ($printable): array {
            $profile = $printable['profiles'][$code] ?? [];

            return [$code => [
                'rows_per_page' => max(1, (int) ($profile['rows_per_page'] ?? 0)),
                'grid_rows' => max(1, (int) ($profile['grid_rows'] ?? 0)),
                'last_page_grid_rows' => max(0, (int) ($profile['last_page_grid_rows'] ?? 0)),
                'description_chars_per_line' => max(1, (int) ($profile['description_chars_per_line'] ?? 0)),
            ]];
        })
        ->all();
    $currentRowsPerPage = (int) ($filters['rows_per_page'] ?? ($paperProfile['rows_per_page'] ?? 28));
    $currentGridRows = (int) ($filters['grid_rows'] ?? ($paperProfile['grid_rows'] ?? 28));
    $currentLastPageGridRows = (int) ($filters['last_page_grid_rows'] ?? ($paperProfile['last_page_grid_rows'] ?? 28));
    $currentDescriptionCharsPerLine = (int) ($filters['description_chars_per_line'] ?? ($paperProfile['description_chars_per_line'] ?? 42));
    $layoutHelp = [
        'rows_per_page' => 'Maximum estimated row units placed on each full page before a new page starts.',
        'grid_rows' => 'Total visible grid height for non-final pages, including any filler rows.',
        'last_page_grid_rows' => 'Target grid height for the last page before the signatory section; blank rows are added until it is reached.',
        'description_chars_per_line' => 'Wrap estimate for long text. Lower values assume earlier wrapping; higher values pack more text into one row.',
    ];
    $pdfParams = array_filter([
        'paper_profile' => $selectedPaper,
        'rows_per_page' => $filters['rows_per_page'] ?? null,
        'grid_rows' => $filters['grid_rows'] ?? null,
        'last_page_grid_rows' => $filters['last_page_grid_rows'] ?? null,
        'description_chars_per_line' => $filters['description_chars_per_line'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
    $archiveDocumentNumber = trim((string) ($document['ics_no'] ?? ''));
@endphp

<x-print.panel
    kicker="ICS"
    title="Inventory Custodian Slip"
    copy="Choose the paper profile, review the ICS preview on the right, then print or download the PDF."
>
    <div class="core-print-sidebar">
        <div class="core-print-sidebar__intro">
            <div class="core-print-sidebar__eyebrow">Preview Controls</div>
            <p class="core-print-sidebar__help">
                ICS print preview is rendered from the finalized record and follows the selected paper profile.
            </p>
        </div>

        @include('gso::print.partials.archive-feedback', ['documentType' => 'ICS'])

        <form
            method="GET"
            action="{{ route('gso.ics.print', ['ics' => $ics['id'] ?? '']) }}"
            class="core-print-sidebar__form"
            data-ics-print-form="1"
            data-ics-print-paper-defaults='@json($paperDefaults)'
        >
            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Preview Settings</div>

                <div class="core-print-sidebar__field">
                    <label class="form-label">Paper Size</label>
                    <select
                        name="paper_profile"
                        class="form-control"
                        data-ics-print-paper-select="1"
                        @disabled(count($paperOptions) <= 1)
                    >
                        @foreach ($paperOptions as $code => $label)
                            <option value="{{ $code }}" @selected($selectedPaper === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Layout Settings</div>

                <p class="core-print-sidebar__note">
                    Tune the page row budget for this preview. Reset anytime to return to the paper defaults.
                </p>

                <div class="core-print-sidebar__field-grid">
                    <div class="core-print-sidebar__field">
                        <label class="form-label core-print-sidebar__label">
                            <span>Rows / Page</span>
                            <span
                                class="core-print-sidebar__tooltip"
                                tabindex="0"
                                role="img"
                                aria-label="{{ $layoutHelp['rows_per_page'] }}"
                                title="{{ $layoutHelp['rows_per_page'] }}"
                            >
                                <i class="ri-information-line"></i>
                            </span>
                        </label>
                        <input
                            type="number"
                            min="1"
                            max="200"
                            name="rows_per_page"
                            value="{{ $currentRowsPerPage }}"
                            class="form-control"
                            data-ics-print-setting="rows_per_page"
                        >
                    </div>

                    <div class="core-print-sidebar__field">
                        <label class="form-label core-print-sidebar__label">
                            <span>Grid Rows</span>
                            <span
                                class="core-print-sidebar__tooltip"
                                tabindex="0"
                                role="img"
                                aria-label="{{ $layoutHelp['grid_rows'] }}"
                                title="{{ $layoutHelp['grid_rows'] }}"
                            >
                                <i class="ri-information-line"></i>
                            </span>
                        </label>
                        <input
                            type="number"
                            min="1"
                            max="200"
                            name="grid_rows"
                            value="{{ $currentGridRows }}"
                            class="form-control"
                            data-ics-print-setting="grid_rows"
                        >
                    </div>

                    <div class="core-print-sidebar__field">
                        <label class="form-label core-print-sidebar__label">
                            <span>Last Page Grid</span>
                            <span
                                class="core-print-sidebar__tooltip"
                                tabindex="0"
                                role="img"
                                aria-label="{{ $layoutHelp['last_page_grid_rows'] }}"
                                title="{{ $layoutHelp['last_page_grid_rows'] }}"
                            >
                                <i class="ri-information-line"></i>
                            </span>
                        </label>
                        <input
                            type="number"
                            min="0"
                            max="200"
                            name="last_page_grid_rows"
                            value="{{ $currentLastPageGridRows }}"
                            class="form-control"
                            data-ics-print-setting="last_page_grid_rows"
                        >
                    </div>

                    <div class="core-print-sidebar__field">
                        <label class="form-label core-print-sidebar__label">
                            <span>Wrap Width</span>
                            <span
                                class="core-print-sidebar__tooltip"
                                tabindex="0"
                                role="img"
                                aria-label="{{ $layoutHelp['description_chars_per_line'] }}"
                                title="{{ $layoutHelp['description_chars_per_line'] }}"
                            >
                                <i class="ri-information-line"></i>
                            </span>
                        </label>
                        <input
                            type="number"
                            min="10"
                            max="300"
                            name="description_chars_per_line"
                            value="{{ $currentDescriptionCharsPerLine }}"
                            class="form-control"
                            data-ics-print-setting="description_chars_per_line"
                        >
                    </div>
                </div>

                <button type="button" class="core-print-sidebar__link-button" data-ics-print-apply-defaults="1">
                    Use Selected Paper Defaults
                </button>
            </div>

            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Preview Stats</div>

                <div class="core-print-sidebar__stats">
                    <div class="core-print-sidebar__stat">
                        <span class="core-print-sidebar__stat-label">Estimated Pages</span>
                        <strong class="core-print-sidebar__stat-value">{{ number_format((int) ($pagination['page_count'] ?? 1)) }}</strong>
                    </div>

                    <div class="core-print-sidebar__stat">
                        <span class="core-print-sidebar__stat-label">Item Lines</span>
                        <strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['line_items'] ?? 0)) }}</strong>
                    </div>

                    <div class="core-print-sidebar__stat">
                        <span class="core-print-sidebar__stat-label">Printed Rows</span>
                        <strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['printed_rows'] ?? 0)) }}</strong>
                    </div>

                    <div class="core-print-sidebar__stat">
                        <span class="core-print-sidebar__stat-label">Page Usage</span>
                        <strong class="core-print-sidebar__stat-value">
                            {{ collect($pagination['page_used_units'] ?? [])->map(fn ($value) => number_format((int) $value))->implode(' / ') ?: '0' }}
                        </strong>
                    </div>

                    <div class="core-print-sidebar__stat">
                        <span class="core-print-sidebar__stat-label">Last-Page Filler</span>
                        <strong class="core-print-sidebar__stat-value">{{ number_format((int) ($pagination['last_page_padding'] ?? 0)) }}</strong>
                    </div>

                    <div class="core-print-sidebar__stat">
                        <span class="core-print-sidebar__stat-label">Total Quantity</span>
                        <strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['quantity_total'] ?? 0)) }}</strong>
                    </div>

                    <div class="core-print-sidebar__stat">
                        <span class="core-print-sidebar__stat-label">Total Amount</span>
                        <strong class="core-print-sidebar__stat-value">{{ number_format((float) ($summary['amount_total'] ?? 0), 2) }}</strong>
                    </div>
                </div>
            </div>

            <div class="core-print-sidebar__section core-print-sidebar__section--actions">
                <div class="core-print-sidebar__section-title">Actions</div>

                <div class="core-print-sidebar__actions">
                    <button type="submit" class="ti-btn btn-wave ti-btn-primary-full w-full">
                        Update Preview
                    </button>

                    <a
                        href="{{ route('gso.ics.print.pdf', ['ics' => $ics['id'] ?? ''] + $pdfParams) }}"
                        data-ics-print-pdf-download="1"
                        data-ics-print-pdf-base="{{ route('gso.ics.print.pdf', ['ics' => $ics['id'] ?? '']) }}"
                        class="ti-btn btn-wave ti-btn-outline-primary label-ti-btn w-full text-center"
                    >
                        <i class="ri-file-pdf-line label-ti-btn-icon me-2"></i>
                        Download PDF
                    </a>

                    @include('gso::print.partials.archive-action', [
                        'documentType' => 'ICS',
                        'documentNumber' => $archiveDocumentNumber,
                        'archiveRoute' => 'gso.ics.print.archive',
                        'archiveViewRoute' => 'gso.ics.print.archive.view',
                        'archiveRecord' => $signedArchive ?? null,
                        'routeParams' => ['ics' => $ics['id'] ?? ''],
                        'pdfParams' => $pdfParams,
                    ])

                    <a href="{{ route('gso.ics.print', ['ics' => $ics['id'] ?? '']) }}" class="core-print-sidebar__reset">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-print.panel>
