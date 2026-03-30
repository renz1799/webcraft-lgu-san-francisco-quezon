@php
    $summary = $report['summary'] ?? [];
    $selectedInventoryItems = collect($selectedInventoryItems ?? []);
    $selectedStickers = collect($selectedStickers ?? []);
    $searchPlaceholder = 'Search property no. or stock no...';
    $selectedInventoryItemIds = collect($filters['inventory_item_ids'] ?? [])
        ->when(
            ! is_array($filters['inventory_item_ids'] ?? null),
            fn ($collection) => collect([$filters['inventory_item_ids'] ?? null])
        )
        ->flatten(1)
        ->filter()
        ->map(fn ($value) => (string) $value);

    if ($selectedInventoryItemIds->isEmpty() && $selectedInventoryItems->isNotEmpty()) {
        $selectedInventoryItemIds = $selectedInventoryItems->pluck('id')->map(fn ($value) => (string) $value);
    }

    $selectedStickerPreview = $selectedStickers->take(6)->values();
    $remainingSelectedStickerCount = max(0, $selectedStickers->count() - $selectedStickerPreview->count());
    $copies = max(1, (int) ($controls['copies'] ?? 2));
    $showCutGuides = (bool) ($controls['show_cut_guides'] ?? true);
    $pdfParams = array_filter([
        'inventory_item_ids' => $selectedInventoryItemIds->all(),
        'copies' => $copies,
        'show_cut_guides' => $showCutGuides ? 1 : 0,
    ], static fn ($value) => $value !== null && $value !== '' && $value !== []);

    $inventoryItemSelectConfig = [
        'placeholder' => $searchPlaceholder,
        'hasSearch' => false,
        'toggleTag' => '<button type="button"></button>',
        'toggleClasses' => 'hs-select-disabled:pointer-events-none hs-select-disabled:opacity-50 relative flex text-nowrap w-full cursor-pointer bg-white border border-gray-200 rounded-sm text-start text-sm focus:border-primary focus:ring-primary dark:bg-bodybg dark:border-white/10 dark:text-white/70 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-primary',
        'dropdownClasses' => 'mt-2 z-40 w-full max-h-72 p-1 space-y-0.5 bg-white border border-gray-200 rounded-sm overflow-hidden overflow-y-auto dark:bg-bodybg dark:border-white/10',
        'optionClasses' => 'py-2 px-4 w-full text-sm text-gray-800 cursor-pointer hover:bg-gray-100 rounded-sm focus:outline-none focus:bg-gray-100 dark:bg-bodybg dark:hover:bg-bodybg dark:text-gray-200 dark:focus:bg-bodybg',
        'mode' => 'tags',
        'tagsClasses' => 'relative ps-1 pe-9 py-1 min-h-[52px] flex flex-wrap items-center gap-1 content-start w-full border border-gray-200 rounded-sm text-start text-sm cursor-text focus-within:border-primary focus-within:ring-1 focus-within:ring-primary dark:bg-bodybg dark:border-white/10 dark:text-white/70',
        'tagsItemClasses' => 'inline-flex max-w-full flex-none order-1',
        'tagsItemTemplate' => '<div class="flex items-center max-w-full relative z-10 bg-white border border-gray-200 rounded-full px-2 py-1 dark:bg-bodybg dark:border-white/10"><div class="size-6 shrink-0 me-1 flex items-center justify-center" data-icon></div><div class="truncate text-xs font-semibold leading-none" data-title></div><div class="inline-flex flex-shrink-0 justify-center items-center size-5 ms-2 rounded-full text-gray-800 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-white/70 text-sm dark:bg-bodybg/50 dark:hover:bg-bodybg dark:text-white/70 cursor-pointer" data-remove><svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></div></div>',
        'tagsInputClasses' => 'order-2 min-w-[12rem] grow basis-[12rem] bg-transparent border-0 px-2 py-2 text-sm leading-none focus:outline-none focus:ring-0 dark:bg-bodybg dark:text-white/70 placeholder:text-gray-400 dark:placeholder:text-white/50',
        'searchNoResultText' => 'No inventory item found',
        'searchNoResultClasses' => 'px-3 py-2 text-xs text-gray-500 dark:text-white/50',
        'optionTemplate' => '<div class="flex items-center"><div class="size-8 me-2 flex items-center justify-center" data-icon></div><div class="min-w-0"><div class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate" data-title></div><div class="text-xs text-gray-500 dark:text-white/70 truncate" data-description></div></div><div class="ms-auto"><span class="hidden hs-selected:block"><svg class="flex-shrink-0 size-4 text-primary" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/></svg></span></div></div>',
    ];
@endphp

<x-print.panel
    kicker="Reports"
    title="Sticker Printing"
    copy="Select one or more inventory assets, set the number of sticker copies, then review the A4 sticker sheet before downloading the PDF."
>
    <div class="core-print-sidebar">
        <div class="core-print-sidebar__intro">
            <div class="core-print-sidebar__eyebrow">Preview Controls</div>
            <p class="core-print-sidebar__help">This page keeps the legacy sticker sheet layout, but it now lives under the GSO Reports workspace.</p>
        </div>

        <form
            method="GET"
            action="{{ route('gso.reports.stickers.print') }}"
            class="core-print-sidebar__form"
            data-stickers-print-form="1"
            data-stickers-print-paper-defaults="{}"
        >
            <input type="hidden" name="preview" value="1">

            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Sticker Sheet</div>

                <div class="core-print-sidebar__field">
                    <label class="form-label" for="stickers-inventory-item">Inventory Items</label>
                    <div class="relative">
                        <select
                            id="stickers-inventory-item"
                            name="inventory_item_ids[]"
                            multiple
                            data-stickers-inventory-select="1"
                            data-stickers-search-placeholder="{{ $searchPlaceholder }}"
                            data-hs-select='@json($inventoryItemSelectConfig)'
                            class="hidden"
                        >
                            @foreach ($availableInventoryItems as $inventoryItem)
                                @php
                                    $optionTitle = (string) ($inventoryItem['title'] ?? $inventoryItem['label'] ?? 'Inventory Item');
                                    $optionDescription = (string) ($inventoryItem['description'] ?? '');
                                    $classification = strtoupper((string) ($inventoryItem['classification'] ?? 'PPE'));
                                    $badgeClasses = $classification === 'ICS'
                                        ? 'inline-flex size-6 items-center justify-center rounded-full bg-warning/10 text-[10px] font-semibold uppercase leading-none tracking-wide text-warning'
                                        : 'inline-flex size-6 items-center justify-center rounded-full bg-primary/10 text-[10px] font-semibold uppercase leading-none tracking-wide text-primary';
                                    $iconMarkup = '<span class="' . $badgeClasses . '">' . e($classification) . '</span>';
                                @endphp
                                <option
                                    value="{{ $inventoryItem['id'] }}"
                                    @selected($selectedInventoryItemIds->contains((string) ($inventoryItem['id'] ?? '')))
                                    data-hs-select-option='@json([
                                        "description" => $optionDescription,
                                        "icon" => $iconMarkup,
                                    ])'
                                >
                                    {{ $optionTitle }}
                                </option>
                            @endforeach
                        </select>

                        <div class="absolute top-1/2 end-3 -translate-y-1/2 pointer-events-none">
                            <svg class="flex-shrink-0 size-3.5 text-gray-500 dark:text-white/70" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5" /><path d="m7 9 5-5 5 5" /></svg>
                        </div>
                    </div>
                    <p class="core-print-sidebar__note">Search by property number first, then tag as many different assets as you need for this sticker batch.</p>
                </div>

                <div class="core-print-sidebar__field">
                    <label class="form-label" for="stickers-copies">Number of Copies</label>
                    <input
                        id="stickers-copies"
                        type="number"
                        name="copies"
                        min="1"
                        max="24"
                        value="{{ $copies }}"
                        class="form-control"
                    >
                    <p class="core-print-sidebar__note">Each selected asset will be repeated this many times on the sticker sheet.</p>
                </div>

                <label class="core-print-sidebar__toggle">
                    <input type="checkbox" name="show_cut_guides" value="1" @checked($showCutGuides)>
                    <span>Show cut guides and trim marks around each sticker in the preview and downloaded PDF.</span>
                </label>
            </div>

            <div class="core-print-sidebar__section">
                <div class="core-print-sidebar__section-title">Preview Stats</div>
                <div class="core-print-sidebar__stats">
                    <div class="core-print-sidebar__stat">
                        <span class="core-print-sidebar__stat-label">Selection</span>
                        <strong class="core-print-sidebar__stat-value">{{ $summary['selected_asset'] ?? 'No asset selected' }}</strong>
                    </div>
                    <div class="core-print-sidebar__stat">
                        <span class="core-print-sidebar__stat-label">Selected Assets</span>
                        <strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['selected_assets_count'] ?? 0)) }}</strong>
                    </div>
                    <div class="core-print-sidebar__stat">
                        <span class="core-print-sidebar__stat-label">Classification</span>
                        <strong class="core-print-sidebar__stat-value">{{ $summary['classification'] ?? '-' }}</strong>
                    </div>
                    <div class="core-print-sidebar__stat">
                        <span class="core-print-sidebar__stat-label">Copies / Asset</span>
                        <strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['copies'] ?? 0)) }}</strong>
                    </div>
                    <div class="core-print-sidebar__stat">
                        <span class="core-print-sidebar__stat-label">Total Stickers</span>
                        <strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['total_stickers'] ?? 0)) }}</strong>
                    </div>
                    <div class="core-print-sidebar__stat">
                        <span class="core-print-sidebar__stat-label">Estimated Pages</span>
                        <strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['page_count'] ?? 0)) }}</strong>
                    </div>
                    <div class="core-print-sidebar__stat">
                        <span class="core-print-sidebar__stat-label">Stickers / Page</span>
                        <strong class="core-print-sidebar__stat-value">{{ number_format((int) ($summary['stickers_per_page'] ?? 8)) }}</strong>
                    </div>
                </div>
            </div>

            @if ($selectedStickerPreview->isNotEmpty())
                <div class="core-print-sidebar__section">
                    <div class="core-print-sidebar__section-title">Selected Assets</div>
                    <div class="core-print-sidebar__stats">
                        @foreach ($selectedStickerPreview as $selectedSticker)
                            <div class="core-print-sidebar__stat">
                                <span class="core-print-sidebar__stat-label">{{ $selectedSticker['reference'] ?? 'N/A' }}</span>
                                <strong class="core-print-sidebar__stat-value">{{ $selectedSticker['description'] ?? 'N/A' }}</strong>
                                <span class="text-xs text-muted">{{ $selectedSticker['type_label'] ?? 'N/A' }} | {{ $selectedSticker['office_label'] ?? 'UNASSIGNED OFFICE' }}</span>
                            </div>
                        @endforeach
                        @if ($remainingSelectedStickerCount > 0)
                            <div class="core-print-sidebar__stat">
                                <span class="core-print-sidebar__stat-label">More Selected</span>
                                <strong class="core-print-sidebar__stat-value">+{{ number_format($remainingSelectedStickerCount) }} more asset{{ $remainingSelectedStickerCount === 1 ? '' : 's' }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="core-print-sidebar__section core-print-sidebar__section--actions">
                <div class="core-print-sidebar__section-title">Actions</div>
                <div class="core-print-sidebar__actions">
                    <button type="submit" class="ti-btn btn-wave ti-btn-primary-full w-full">Update Preview</button>
                    <a
                        href="{{ route('gso.reports.stickers.print.pdf', $pdfParams) }}"
                        data-stickers-print-pdf-download="1"
                        data-stickers-print-pdf-base="{{ route('gso.reports.stickers.print.pdf') }}"
                        class="ti-btn btn-wave ti-btn-outline-primary label-ti-btn w-full text-center {{ $selectedStickerPreview->isEmpty() ? 'pointer-events-none opacity-50' : '' }}"
                        @if($selectedStickerPreview->isEmpty()) aria-disabled="true" @endif
                    >
                        <i class="ri-file-pdf-line label-ti-btn-icon me-2"></i>
                        Download PDF
                    </a>
                    @if ($selectedStickerPreview->count() === 1 && $sticker)
                        <a href="{{ $sticker['inventory_item_url'] }}" class="ti-btn btn-wave ti-btn-outline-primary w-full text-center">
                            Open Inventory Item
                        </a>
                        <a href="{{ $sticker['public_asset_url'] }}" target="_blank" rel="noopener" class="ti-btn btn-wave ti-btn-outline-primary w-full text-center">
                            Public Asset Page
                        </a>
                    @endif
                    <a href="{{ route('gso.reports.stickers.print', ['preview' => 1]) }}" class="core-print-sidebar__reset">Reset</a>
                </div>
            </div>
        </form>
    </div>
</x-print.panel>
