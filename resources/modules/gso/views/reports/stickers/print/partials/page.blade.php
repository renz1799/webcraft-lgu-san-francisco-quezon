@php
    $stickersPerRow = max(1, (int) ($sheet['stickers_per_row'] ?? 2));
    $showCutGuides = (bool) ($controls['show_cut_guides'] ?? true);
    $rows = collect($pageStickers ?? [])->chunk($stickersPerRow)->values();
@endphp

<div class="page print-page sticker-sheet-page">
    <div class="sticker-sheet-content">
        <div class="sticker-sheet-grid">
            @foreach ($rows as $row)
                <div class="sticker-sheet-row">
                    @foreach ($row as $copySticker)
                        @include('gso::reports.stickers.print.partials.sticker', [
                            'sticker' => $copySticker,
                            'showCutGuides' => $showCutGuides,
                        ])
                    @endforeach

                    @for ($emptySlots = $row->count(); $emptySlots < $stickersPerRow; $emptySlots++)
                        <div class="sticker-card sticker-card--ghost" aria-hidden="true"></div>
                    @endfor
                </div>
            @endforeach
        </div>

        <div class="sticker-sheet-page-number">Page {{ $pageNo }} of {{ $totalPages }}</div>
    </div>
</div>
