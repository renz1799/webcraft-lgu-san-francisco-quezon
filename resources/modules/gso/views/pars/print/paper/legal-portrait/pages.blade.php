@php
    $pages = $report['pagination']['pages'] ?? [['rows' => $report['rows'] ?? [], 'used_units' => count($report['rows'] ?? [])]];
    $gridRows = max(1, (int) ($paperProfile['grid_rows'] ?? 30));
    $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 0));
    $totalPages = count($pages);
@endphp

@foreach ($pages as $pageIndex => $page)
    <div class="gso-par-print-page">
        @include('gso::pars.print.partials.header', [
            'report' => $report,
            'headerImage' => $headerImage,
            'continuationFromPage' => $totalPages > 1 && $pageIndex > 0 ? $pageIndex : null,
        ])

        <div class="gso-par-print-body">
            @include('gso::pars.print.partials.meta', [
                'report' => $report,
            ])

            @include('gso::pars.print.partials.table', [
                'rows' => $page['rows'] ?? [],
                'gridRows' => $gridRows,
                'lastPageGridRows' => $lastPageGridRows,
                'usedGridUnits' => (int) ($page['used_units'] ?? 0),
                'isLastPage' => $pageIndex === ($totalPages - 1),
            ])

            @if ($pageIndex === ($totalPages - 1))
                @include('gso::pars.print.partials.signatures', [
                    'report' => $report,
                ])
            @endif
        </div>

        @include('gso::pars.print.partials.footer', [
            'pageIndex' => $pageIndex,
            'totalPages' => $totalPages,
            'footerImage' => $footerImage,
            'continuedToPage' => $totalPages > 1 && $pageIndex < ($totalPages - 1) ? ($pageIndex + 2) : null,
        ])
    </div>
@endforeach
