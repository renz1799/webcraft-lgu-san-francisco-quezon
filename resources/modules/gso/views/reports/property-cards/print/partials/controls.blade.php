@php
    $printConfig = app(\App\Core\Services\Contracts\Print\PrintConfigLoaderInterface::class);
    $document = $report['document'] ?? [];
    $summary = $document['summary'] ?? [];
    $scope = $document['filters'] ?? [];
    $selectedPaper = $filters['paper_profile'] ?? ($paperProfile['code'] ?? $printConfig->defaultPaper('gso_property_cards', 'a4-landscape'));
    $allowedPapers = $printConfig->allowedPapers('gso_property_cards', 'a4-landscape');
    $paperOptions = collect($allowedPapers)->mapWithKeys(fn ($code) => [$code => config("print.papers.{$code}.label", $code)])->all();
    $pdfParams = array_filter([
        'paper_profile' => $selectedPaper,
        'page' => $filters['page'] ?? null,
        'size' => $filters['size'] ?? null,
        'search' => $filters['search'] ?? null,
        'inventory_item_id' => $filters['inventory_item_id'] ?? null,
        'department_id' => $filters['department_id'] ?? null,
        'item_id' => $filters['item_id'] ?? null,
        'fund_source_id' => $filters['fund_source_id'] ?? null,
        'classification' => $filters['classification'] ?? null,
        'custody_state' => $filters['custody_state'] ?? null,
        'inventory_status' => $filters['inventory_status'] ?? null,
        'archived' => $filters['archived'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<x-print.panel
    kicker="Property Cards"
    title="Property Cards"
    copy="Preview a page of inventory item property cards with the current report filters, then download the batch as a landscape PDF."
>
    <div class="core-print-sidebar">
        <div class="core-print-sidebar__intro">
            <div class="core-print-sidebar__eyebrow">Preview Controls</div>
            <p class="core-print-sidebar__help">Each matching inventory item prints as its own property card page. Use the batch filters here instead of the Inventory Items table.</p>
        </div>

        <form
            method="GET"
            action="{{ route('gso.reports.property-cards.print') }}"
            class="core-print-sidebar__form"
            data-property-cards-print-form="1"
            data-property-cards-print-paper-defaults='{}'
        >
            <input type="hidden" name="preview" value="1">
            @if(!empty($filters['inventory_item_id']))
                <input type="hidden" name="inventory_item_id" value="{{ $filters['inventory_item_id'] }}">
            @endif

            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Preview Settings</div>
                <div class="core-print-sidebar__field">
                    <label class="form-label" for="property-cards-paper">Paper Size</label>
                    <select id="property-cards-paper" name="paper_profile" class="form-control" data-property-cards-print-paper-select="1">
                        @foreach ($paperOptions as $code => $label)
                            <option value="{{ $code }}" @selected($selectedPaper === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Batch Scope</div>
                @if(!empty($filters['inventory_item_id']))
                    <div class="core-print-sidebar__field">
                        <label class="form-label">Focused Inventory Item</label>
                        <div class="core-print-sidebar__stat">
                            <span class="core-print-sidebar__stat-label">Current Asset</span>
                            <strong class="core-print-sidebar__stat-value">{{ $scope['inventory_item'] ?? 'Selected Inventory Item' }}</strong>
                        </div>
                    </div>
                @endif
                <div class="core-print-sidebar__field">
                    <label class="form-label" for="property-cards-search">Search</label>
                    <input
                        id="property-cards-search"
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        class="form-control"
                        placeholder="Property no., stock no., item, serial..."
                    >
                </div>

                <div class="core-print-sidebar__field-grid">
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="property-cards-page">Source Page</label>
                        <input id="property-cards-page" type="number" min="1" max="500" name="page" value="{{ $filters['page'] ?? 1 }}" class="form-control">
                    </div>
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="property-cards-size">Cards / Preview</label>
                        <input id="property-cards-size" type="number" min="1" max="50" name="size" value="{{ $filters['size'] ?? 12 }}" class="form-control">
                    </div>
                </div>
            </div>

            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Inventory Filters</div>
                <div class="core-print-sidebar__field">
                    <label class="form-label" for="property-cards-department">Department</label>
                    <select id="property-cards-department" name="department_id" class="form-control">
                        <option value="">All Departments</option>
                        @foreach($availableDepartments as $department)
                            <option value="{{ $department['id'] }}" @selected((string) ($department['id'] ?? '') === (string) ($filters['department_id'] ?? ''))>{{ $department['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="core-print-sidebar__field">
                    <label class="form-label" for="property-cards-item">Item</label>
                    <select id="property-cards-item" name="item_id" class="form-control">
                        <option value="">All Items</option>
                        @foreach($availableItems as $item)
                            <option value="{{ $item['id'] }}" @selected((string) ($item['id'] ?? '') === (string) ($filters['item_id'] ?? ''))>{{ $item['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="core-print-sidebar__field">
                    <label class="form-label" for="property-cards-fund-source">Fund Source</label>
                    <select id="property-cards-fund-source" name="fund_source_id" class="form-control">
                        <option value="">All Fund Sources</option>
                        @foreach($availableFunds as $fund)
                            <option value="{{ $fund['id'] }}" @selected((string) ($fund['id'] ?? '') === (string) ($filters['fund_source_id'] ?? ''))>{{ $fund['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="core-print-sidebar__field-grid">
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="property-cards-classification">Classification</label>
                        <select id="property-cards-classification" name="classification" class="form-control">
                            <option value="">All Classes</option>
                            @foreach($classificationOptions as $value => $label)
                                <option value="{{ $value }}" @selected((string) $value === (string) ($filters['classification'] ?? ''))>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="property-cards-custody">Custody</label>
                        <select id="property-cards-custody" name="custody_state" class="form-control">
                            <option value="">All Custody</option>
                            @foreach($custodyOptions as $value => $label)
                                <option value="{{ $value }}" @selected((string) $value === (string) ($filters['custody_state'] ?? ''))>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="core-print-sidebar__field-grid">
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="property-cards-status">Inventory Status</label>
                        <select id="property-cards-status" name="inventory_status" class="form-control">
                            <option value="">All Statuses</option>
                            @foreach($inventoryStatusOptions as $value => $label)
                                <option value="{{ $value }}" @selected((string) $value === (string) ($filters['inventory_status'] ?? ''))>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="core-print-sidebar__field">
                        <label class="form-label" for="property-cards-record-status">Record Status</label>
                        <select id="property-cards-record-status" name="archived" class="form-control">
                            @foreach($recordStatusOptions as $value => $label)
                                <option value="{{ $value }}" @selected((string) $value === (string) ($filters['archived'] ?? 'active'))>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Preview Stats</div>
                <div class="core-print-sidebar__stats">
                    <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">Matching Items</span><strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['total_matching'] ?? 0)) }}</strong></div>
                    <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">Cards in Preview</span><strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['cards_in_batch'] ?? 0)) }}</strong></div>
                    <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">Source Page</span><strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['source_page'] ?? 1)) }}</strong></div>
                    <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">Cards / Preview</span><strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['source_page_size'] ?? 12)) }}</strong></div>
                    <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">PPE Cards</span><strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['ppe_cards'] ?? 0)) }}</strong></div>
                    <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">ICS Cards</span><strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['ics_cards'] ?? 0)) }}</strong></div>
                    @if(!empty($filters['inventory_item_id']))
                        <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">Focused Asset</span><strong class="core-print-sidebar__stat-value">{{ $scope['inventory_item'] ?? 'Selected Inventory Item' }}</strong></div>
                    @endif
                    <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">Department Scope</span><strong class="core-print-sidebar__stat-value">{{ $scope['department'] ?? 'All Departments' }}</strong></div>
                    <div class="core-print-sidebar__stat"><span class="core-print-sidebar__stat-label">Fund Scope</span><strong class="core-print-sidebar__stat-value">{{ $scope['fund_source'] ?? 'All Fund Sources' }}</strong></div>
                </div>
            </div>

            <div class="core-print-sidebar__section core-print-sidebar__section--actions">
                <div class="core-print-sidebar__section-title">Actions</div>
                <div class="core-print-sidebar__actions">
                    <button type="submit" class="ti-btn btn-wave ti-btn-primary-full w-full">Update Preview</button>
                    <a
                        href="{{ route('gso.reports.property-cards.print.pdf', $pdfParams) }}"
                        data-property-cards-print-pdf-download="1"
                        data-property-cards-print-pdf-base="{{ route('gso.reports.property-cards.print.pdf') }}"
                        class="ti-btn btn-wave ti-btn-outline-primary label-ti-btn w-full text-center"
                    >
                        <i class="ri-file-pdf-line label-ti-btn-icon me-2"></i>
                        Download PDF
                    </a>
                    <a href="{{ route('gso.reports.property-cards.print', ['preview' => 1]) }}" class="core-print-sidebar__reset">Reset</a>
                </div>
            </div>
        </form>
    </div>
</x-print.panel>
