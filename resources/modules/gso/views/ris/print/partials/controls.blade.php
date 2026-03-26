@php
    $printConfig = app(\App\Core\Services\Contracts\Print\PrintConfigLoaderInterface::class);
    $ris = $report['ris'] ?? [];
    $document = $report['document'] ?? [];
    $summary = $document['summary'] ?? [];
    $selectedPaper = $filters['paper_profile'] ?? ($paperProfile['code'] ?? $printConfig->defaultPaper('gso_ris', 'a4-portrait'));
    $allowedPapers = $printConfig->allowedPapers('gso_ris', 'a4-portrait');
    $paperOptions = collect($allowedPapers)
        ->mapWithKeys(fn ($code) => [$code => config("print.papers.{$code}.label", $code)])
        ->all();

    $rowsPerPage = max(1, (int) ($paperProfile['rows_per_page'] ?? 24));
    $pageCount = max(1, count(array_chunk($report['rows'] ?? [], $rowsPerPage)));
    $pdfParams = array_filter([
        'paper_profile' => $selectedPaper,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<x-print.panel
    kicker="RIS"
    title="Requisition and Issue Slip"
    copy="Choose the paper profile, review the RIS preview on the right, then print or download the PDF."
>
    <div class="core-print-sidebar">
        <div class="core-print-sidebar__intro">
            <div class="core-print-sidebar__eyebrow">Preview Controls</div>
            <p class="core-print-sidebar__help">
                RIS print preview is rendered from the saved record and follows the selected paper profile.
            </p>
        </div>

        <form method="GET" action="{{ route('gso.ris.print', ['ris' => $ris['id'] ?? '']) }}" class="core-print-sidebar__form">
            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Preview Settings</div>

                <div class="core-print-sidebar__field">
                    <label class="form-label">Paper Size</label>
                    <select
                        name="paper_profile"
                        class="form-control"
                        @disabled(count($paperOptions) <= 1)
                    >
                        @foreach ($paperOptions as $code => $label)
                            <option value="{{ $code }}" @selected($selectedPaper === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Preview Stats</div>

                <div class="core-print-sidebar__field">
                    <label class="form-label">Pages</label>
                    <input type="text" class="form-control" value="{{ number_format($pageCount) }}" readonly>
                </div>

                <div class="core-print-sidebar__field">
                    <label class="form-label">Item Lines</label>
                    <input type="text" class="form-control" value="{{ number_format((int) ($summary['line_items'] ?? 0)) }}" readonly>
                </div>

                <div class="core-print-sidebar__field">
                    <label class="form-label">Total Requested</label>
                    <input type="text" class="form-control" value="{{ number_format((int) ($summary['qty_requested_total'] ?? 0)) }}" readonly>
                </div>

                <div class="core-print-sidebar__field">
                    <label class="form-label">Total Issued</label>
                    <input type="text" class="form-control" value="{{ number_format((int) ($summary['qty_issued_total'] ?? 0)) }}" readonly>
                </div>

                <div class="core-print-sidebar__field">
                    <label class="form-label">Status</label>
                    <input type="text" class="form-control" value="{{ $ris['status_text'] ?? 'DRAFT' }}" readonly>
                </div>
            </div>

            <div class="core-print-sidebar__section core-print-sidebar__section--actions">
                <div class="core-print-sidebar__section-title">Actions</div>

                <div class="core-print-sidebar__actions">
                    <button type="submit" class="ti-btn btn-wave ti-btn-primary-full w-full">
                        Update Preview
                    </button>

                    <a
                        href="{{ route('gso.ris.print.pdf', ['ris' => $ris['id'] ?? ''] + $pdfParams) }}"
                        data-ris-print-pdf-download="1"
                        class="ti-btn btn-wave ti-btn-outline-primary label-ti-btn w-full text-center"
                    >
                        <i class="ri-file-pdf-line label-ti-btn-icon me-2"></i>
                        Download PDF
                    </a>

                    <a href="{{ route('gso.ris.print', ['ris' => $ris['id'] ?? '']) }}" class="core-print-sidebar__reset">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-print.panel>
