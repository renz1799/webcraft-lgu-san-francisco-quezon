@php
    $pages = $report['pagination']['pages'] ?? [['rows' => $report['rows'] ?? [], 'used_units' => count($report['rows'] ?? [])]];
    $gridRows = max(1, (int) ($paperProfile['grid_rows'] ?? 24));
    $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 0));
    $totalPages = count($pages);
@endphp

@foreach ($pages as $pageIndex => $page)
    <div class="gso-ris-print-page">
        @include('gso::ris.print.partials.header', [
            'report' => $report,
            'headerImage' => $headerImage,
            'continuationFromPage' => $totalPages > 1 && $pageIndex > 0 ? $pageIndex : null,
        ])

        <div class="gso-ris-print-body">
            @include('gso::ris.print.partials.meta', [
                'report' => $report,
            ])

            @include('gso::ris.print.partials.table', [
                'report' => $report,
                'rows' => $page['rows'] ?? [],
                'gridRows' => $gridRows,
                'lastPageGridRows' => $lastPageGridRows,
                'usedGridUnits' => (int) ($page['used_units'] ?? 0),
                'isLastPage' => $pageIndex === ($totalPages - 1),
            ])

            @if ($pageIndex === ($totalPages - 1))
                @include('gso::ris.print.partials.signatures', [
                    'report' => $report,
                ])
            @endif
        </div>

        @include('gso::ris.print.partials.footer', [
            'pageIndex' => $pageIndex,
            'totalPages' => $totalPages,
            'footerImage' => $footerImage,
            'continuedToPage' => $totalPages > 1 && $pageIndex < ($totalPages - 1) ? ($pageIndex + 2) : null,
        ])
    </div>
@endforeach
