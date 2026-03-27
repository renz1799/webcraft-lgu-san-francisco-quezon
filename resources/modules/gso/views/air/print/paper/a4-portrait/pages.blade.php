@php
    $pages = $report['pagination']['pages'] ?? [['rows' => $report['rows'] ?? [], 'used_units' => count($report['rows'] ?? [])]];
    $totalPages = count($pages);
    $gridRows = max(
        (int) ($paperProfile['rows_per_page'] ?? 22),
        (int) ($paperProfile['grid_rows'] ?? ($report['max_grid_rows'] ?? 24))
    );
@endphp

@foreach ($pages as $pageIndex => $page)
    <div class="gso-air-print-page">
        @include('gso::air.print.partials.header', [
            'report' => $report,
            'headerImage' => $headerImage,
            'continuationFromPage' => $totalPages > 1 && $pageIndex > 0 ? $pageIndex : null,
        ])

        <div class="gso-air-print-body">
            @include('gso::air.print.partials.table', [
                'report' => $report,
                'rows' => $page['rows'] ?? [],
                'gridRows' => $gridRows,
                'pageIndex' => $pageIndex,
                'totalPages' => $totalPages,
                'fillRows' => $pageIndex < ($totalPages - 1),
                'lastPageGridRows' => (int) ($paperProfile['last_page_grid_rows'] ?? 0),
                'usedGridUnits' => (int) ($page['used_units'] ?? 0),
            ])
        </div>

        @include('gso::air.print.partials.footer', [
            'report' => $report,
            'footerImage' => $footerImage,
            'pageIndex' => $pageIndex,
            'totalPages' => $totalPages,
            'continuedToPage' => $totalPages > 1 && $pageIndex < ($totalPages - 1) ? ($pageIndex + 2) : null,
        ])
    </div>
@endforeach
