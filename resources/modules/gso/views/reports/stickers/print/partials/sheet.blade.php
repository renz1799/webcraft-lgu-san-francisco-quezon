@php
    $stickerCopies = collect($stickers ?? []);
    $stickersPerRow = max(1, (int) ($sheet['stickers_per_row'] ?? 2));
    $stickersPerPage = max(1, (int) ($sheet['stickers_per_page'] ?? 8));
    $showCutGuides = (bool) ($controls['show_cut_guides'] ?? true);
    $pages = $stickerCopies->chunk($stickersPerPage)->values();
@endphp

@if ($stickerCopies->isEmpty())
    @include('gso::reports.stickers.print.partials.empty-page')
@else
    @foreach ($pages as $pageIndex => $pageStickers)
        @include('gso::reports.stickers.print.partials.page', [
            'pageStickers' => $pageStickers,
            'pageNo' => $pageIndex + 1,
            'totalPages' => $pages->count(),
            'controls' => $controls,
            'sheet' => $sheet,
        ])
    @endforeach
@endif
