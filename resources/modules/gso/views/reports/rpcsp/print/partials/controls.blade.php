@php
    $printConfig = app(\App\Core\Services\Contracts\Print\PrintConfigLoaderInterface::class);
    $printable = $printConfig->printable('gso_rpcsp');
    $document = $report['document'] ?? [];
    $summary = $document['summary'] ?? [];
    $signatories = $document['signatories'] ?? [];
    $pagination = $report['pagination']['stats'] ?? [];
    $selectedPaper = $filters['paper_profile'] ?? ($paperProfile['code'] ?? $printConfig->defaultPaper('gso_rpcsp', 'a4-landscape'));
    $allowedPapers = $printConfig->allowedPapers('gso_rpcsp', 'a4-landscape');
    $paperOptions = collect($allowedPapers)->mapWithKeys(fn ($code) => [$code => config("print.papers.{$code}.label", $code)])->all();
    $paperDefaults = collect($allowedPapers)->mapWithKeys(function (string $code) use ($printable): array {
        $profile = $printable['profiles'][$code] ?? [];

        return [$code => [
            'rows_per_page' => max(1, (int) ($profile['rows_per_page'] ?? 0)),
            'grid_rows' => max(1, (int) ($profile['grid_rows'] ?? 0)),
            'last_page_grid_rows' => max(0, (int) ($profile['last_page_grid_rows'] ?? 0)),
            'description_chars_per_line' => max(1, (int) ($profile['description_chars_per_line'] ?? 0)),
        ]];
    })->all();
    $currentRowsPerPage = (int) ($filters['rows_per_page'] ?? ($paperProfile['rows_per_page'] ?? 13));
    $currentGridRows = (int) ($filters['grid_rows'] ?? ($paperProfile['grid_rows'] ?? 13));
    $currentLastPageGridRows = (int) ($filters['last_page_grid_rows'] ?? ($paperProfile['last_page_grid_rows'] ?? 6));
    $currentDescriptionCharsPerLine = (int) ($filters['description_chars_per_line'] ?? ($paperProfile['description_chars_per_line'] ?? 44));
    $layoutHelp = [
        'rows_per_page' => 'Maximum estimated row units placed on each full page before a new page starts.',
        'grid_rows' => 'Target visible grid height for full pages, including any blank filler rows.',
        'last_page_grid_rows' => 'Target visible grid height for the last page before the summary and signature blocks.',
        'description_chars_per_line' => 'Wrap estimate for article, description, property number, and remarks text.',
    ];
    $pdfParams = array_filter([
        'paper_profile' => $selectedPaper,
        'fund_source_id' => $document['fund_source_id'] ?? null,
        'department_id' => $document['department_id'] ?? null,
        'accountable_officer_id' => $document['accountable_officer_id'] ?? null,
        'as_of' => $document['as_of'] ?? null,
        'prefill_count' => !empty($document['prefill_count']) ? 1 : null,
        'accountable_officer_name' => $signatories['accountable_officer_name'] ?? null,
        'accountable_officer_designation' => $signatories['accountable_officer_designation'] ?? null,
        'committee_chair_name' => $signatories['committee_chair_name'] ?? null,
        'committee_member_1_name' => $signatories['committee_member_1_name'] ?? null,
        'committee_member_2_name' => $signatories['committee_member_2_name'] ?? null,
        'approved_by_name' => $signatories['approved_by_name'] ?? null,
        'approved_by_designation' => $signatories['approved_by_designation'] ?? null,
        'verified_by_name' => $signatories['verified_by_name'] ?? null,
        'verified_by_designation' => $signatories['verified_by_designation'] ?? null,
        'rows_per_page' => $filters['rows_per_page'] ?? null,
        'grid_rows' => $filters['grid_rows'] ?? null,
        'last_page_grid_rows' => $filters['last_page_grid_rows'] ?? null,
        'description_chars_per_line' => $filters['description_chars_per_line'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<x-print.panel kicker="RPCSP" title="Physical Count of Semi-Expendable Property" copy="Keep the required RPCSP report filters and signatories here, then tune the landscape preview on the right.">
    <div class="core-print-sidebar">
        <div class="core-print-sidebar__intro">
            <div class="core-print-sidebar__eyebrow">Preview Controls</div>
            <p class="core-print-sidebar__help">RPCSP keeps its required scope filters in the panel while following the shared print workspace flow.</p>
        </div>

        <form method="GET" action="{{ route('gso.reports.rpcsp.print') }}" class="core-print-sidebar__form" data-rpcsp-print-form="1" data-rpcsp-print-paper-defaults='@json($paperDefaults)' data-rpcsp-print-accountable-suggest-url="{{ route('gso.accountable-persons.suggest') }}">
            <input type="hidden" name="preview" value="1">
            <input type="hidden" name="prefill_count" value="0">

            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Preview Settings</div>
                <div class="core-print-sidebar__field">
                    <label class="form-label">Paper Size</label>
                    <select name="paper_profile" class="form-control" data-rpcsp-print-paper-select="1">
                        @foreach ($paperOptions as $code => $label)
                            <option value="{{ $code }}" @selected($selectedPaper === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Layout Settings</div>
                <p class="core-print-sidebar__note">Tune the row budget and wrapping estimate for this preview. Use the paper defaults anytime to reset the layout.</p>

                <div class="core-print-sidebar__field-grid">
                    <div class="core-print-sidebar__field">
                        <label class="form-label core-print-sidebar__label"><span>Rows / Page</span><span class="core-print-sidebar__tooltip" tabindex="0" role="img" aria-label="{{ $layoutHelp['rows_per_page'] }}" title="{{ $layoutHelp['rows_per_page'] }}"><i class="ri-information-line"></i></span></label>
                        <input type="number" min="1" max="200" name="rows_per_page" value="{{ $currentRowsPerPage }}" class="form-control" data-rpcsp-print-setting="rows_per_page">
                    </div>
                    <div class="core-print-sidebar__field">
                        <label class="form-label core-print-sidebar__label"><span>Grid Rows</span><span class="core-print-sidebar__tooltip" tabindex="0" role="img" aria-label="{{ $layoutHelp['grid_rows'] }}" title="{{ $layoutHelp['grid_rows'] }}"><i class="ri-information-line"></i></span></label>
                        <input type="number" min="1" max="200" name="grid_rows" value="{{ $currentGridRows }}" class="form-control" data-rpcsp-print-setting="grid_rows">
                    </div>
                    <div class="core-print-sidebar__field">
                        <label class="form-label core-print-sidebar__label"><span>Last Page Grid</span><span class="core-print-sidebar__tooltip" tabindex="0" role="img" aria-label="{{ $layoutHelp['last_page_grid_rows'] }}" title="{{ $layoutHelp['last_page_grid_rows'] }}"><i class="ri-information-line"></i></span></label>
                        <input type="number" min="0" max="200" name="last_page_grid_rows" value="{{ $currentLastPageGridRows }}" class="form-control" data-rpcsp-print-setting="last_page_grid_rows">
                    </div>
                    <div class="core-print-sidebar__field">
                        <label class="form-label core-print-sidebar__label"><span>Wrap Width</span><span class="core-print-sidebar__tooltip" tabindex="0" role="img" aria-label="{{ $layoutHelp['description_chars_per_line'] }}" title="{{ $layoutHelp['description_chars_per_line'] }}"><i class="ri-information-line"></i></span></label>
                        <input type="number" min="10" max="300" name="description_chars_per_line" value="{{ $currentDescriptionCharsPerLine }}" class="form-control" data-rpcsp-print-setting="description_chars_per_line">
                    </div>
                </div>

                <button type="button" class="core-print-sidebar__link-button" data-rpcsp-print-apply-defaults="1">Use Selected Paper Defaults</button>
            </div>

            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Report Fields</div>
                <div class="core-print-sidebar__field">
                    <label class="form-label" for="rpcsp-fund-source">Fund Source</label>
                    <select id="rpcsp-fund-source" name="fund_source_id" class="form-control">
                        <option value="">All Fund Sources</option>
                        @foreach($availableFunds as $fund)
                            <option value="{{ $fund['id'] }}" @selected((string) ($fund['id'] ?? '') === (string) ($document['fund_source_id'] ?? ''))>{{ $fund['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="core-print-sidebar__field-grid">
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="rpcsp-office">Office</label>
                        <select id="rpcsp-office" name="department_id" class="form-control">
                            <option value="">All Offices</option>
                            @foreach($availableDepartments as $department)
                                <option value="{{ $department['id'] }}" @selected((string) ($department['id'] ?? '') === (string) ($document['department_id'] ?? ''))>{{ $department['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="rpcsp-filter-officer">Accountable Officer</label>
                        <select id="rpcsp-filter-officer" name="accountable_officer_id" class="form-control" data-rpcsp-print-accountable-filter="1">
                            <option value="">All Accountable Officers</option>
                            @foreach($availableAccountableOfficers as $officer)
                                <option value="{{ $officer['id'] }}" data-officer-name="{{ $officer['full_name'] ?? '' }}" data-officer-designation="{{ $officer['designation'] ?? '' }}" @selected((string) ($officer['id'] ?? '') === (string) ($document['accountable_officer_id'] ?? ''))>{{ $officer['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="core-print-sidebar__field-grid">
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="rpcsp-as-of">As of Date</label>
                        <input id="rpcsp-as-of" type="date" name="as_of" value="{{ $document['as_of'] ?? '' }}" class="form-control">
                    </div>
                    <div class="core-print-sidebar__field">
                        <label class="form-label">&nbsp;</label>
                        <label class="core-print-sidebar__toggle">
                            <input type="checkbox" name="prefill_count" value="1" @checked(!empty($document['prefill_count']))>
                            <span>Prefill count and shortage or overage from the book quantity.</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Signatories</div>
                <div class="core-print-sidebar__field-grid">
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="rpcsp-accountable-name">Accountable Signatory</label>
                        <input id="rpcsp-accountable-name" type="text" name="accountable_officer_name" value="{{ $signatories['accountable_officer_name'] ?? '' }}" class="form-control" autocomplete="off" data-rpcsp-print-accountable-name="1">
                        <p class="core-print-sidebar__note">Choose an existing accountable officer to reuse the saved name and designation.</p>
                    </div>
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="rpcsp-accountable-designation">Designation</label>
                        <input id="rpcsp-accountable-designation" type="text" name="accountable_officer_designation" value="{{ $signatories['accountable_officer_designation'] ?? '' }}" class="form-control" data-rpcsp-print-accountable-designation="1">
                    </div>
                </div>

                <div class="core-print-sidebar__field-grid">
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="rpcsp-chair">Committee Chair</label>
                        <input id="rpcsp-chair" type="text" name="committee_chair_name" value="{{ $signatories['committee_chair_name'] ?? '' }}" class="form-control">
                    </div>
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="rpcsp-member-1">Committee Member 1</label>
                        <input id="rpcsp-member-1" type="text" name="committee_member_1_name" value="{{ $signatories['committee_member_1_name'] ?? '' }}" class="form-control">
                    </div>
                </div>

                <div class="core-print-sidebar__field">
                    <label class="form-label" for="rpcsp-member-2">Committee Member 2</label>
                    <input id="rpcsp-member-2" type="text" name="committee_member_2_name" value="{{ $signatories['committee_member_2_name'] ?? '' }}" class="form-control">
                </div>

                <div class="core-print-sidebar__field-grid">
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="rpcsp-approved-name">Approved By</label>
                        <input id="rpcsp-approved-name" type="text" name="approved_by_name" value="{{ $signatories['approved_by_name'] ?? '' }}" class="form-control">
                    </div>
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="rpcsp-approved-designation">Approved Designation</label>
                        <input id="rpcsp-approved-designation" type="text" name="approved_by_designation" value="{{ $signatories['approved_by_designation'] ?? '' }}" class="form-control">
                    </div>
                </div>

                <div class="core-print-sidebar__field-grid">
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="rpcsp-verified-name">Verified By</label>
                        <input id="rpcsp-verified-name" type="text" name="verified_by_name" value="{{ $signatories['verified_by_name'] ?? '' }}" class="form-control">
                    </div>
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="rpcsp-verified-designation">Verified Designation</label>
                        <input id="rpcsp-verified-designation" type="text" name="verified_by_designation" value="{{ $signatories['verified_by_designation'] ?? '' }}" class="form-control">
                    </div>
                </div>
            </div>

            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Preview Stats</div>
                <div class="core-print-sidebar__stats">
                    <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">Estimated Pages</span><strong class="core-print-sidebar__stat-value">{{ number_format((int) ($pagination['page_count'] ?? 1)) }}</strong></div>
                    <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">SE Items</span><strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['total_items'] ?? 0)) }}</strong></div>
                    <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">Qty. per Card</span><strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['total_balance_qty'] ?? 0)) }}</strong></div>
                    <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">Book Value</span><strong class="core-print-sidebar__stat-value">{{ number_format((float) ($summary['total_book_value'] ?? 0), 2) }}</strong></div>
                    <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">Page Usage</span><strong class="core-print-sidebar__stat-value">{{ collect($pagination['page_used_units'] ?? [])->map(fn ($value) => number_format((int) $value))->implode(' / ') ?: '0' }}</strong></div>
                    <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">Last-Page Filler</span><strong class="core-print-sidebar__stat-value">{{ number_format((int) ($pagination['last_page_padding'] ?? 0)) }}</strong></div>
                </div>
            </div>

            <div class="core-print-sidebar__section core-print-sidebar__section--actions">
                <div class="core-print-sidebar__section-title">Actions</div>
                <div class="core-print-sidebar__actions">
                    <button type="submit" class="ti-btn btn-wave ti-btn-primary-full w-full">Update Preview</button>
                    <a href="{{ route('gso.reports.rpcsp.print.pdf', $pdfParams) }}" data-rpcsp-print-pdf-download="1" data-rpcsp-print-pdf-base="{{ route('gso.reports.rpcsp.print.pdf') }}" class="ti-btn btn-wave ti-btn-outline-primary label-ti-btn w-full text-center">
                        <i class="ri-file-pdf-line label-ti-btn-icon me-2"></i>
                        Download PDF
                    </a>
                    <a href="{{ route('gso.reports.rpcsp.print') }}" class="core-print-sidebar__reset">Reset</a>
                </div>
            </div>
        </form>
    </div>
</x-print.panel>
