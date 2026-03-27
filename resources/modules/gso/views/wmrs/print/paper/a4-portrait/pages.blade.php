@php
    $pages = $report['pagination']['pages'] ?? [['rows' => $report['rows'] ?? [], 'used_units' => count($report['rows'] ?? [])]];
    $gridRows = max(1, (int) ($paperProfile['grid_rows'] ?? 10));
    $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 0));
    $totalPages = count($pages);
@endphp

@foreach ($pages as $pageIndex => $page)
    <div class="gso-wmr-print-page">
        @include('gso::wmrs.print.partials.header', [
            'report' => $report,
            'headerImage' => $headerImage,
            'continuationFromPage' => $totalPages > 1 && $pageIndex > 0 ? $pageIndex : null,
        ])

        <div class="gso-wmr-print-body">
            @include('gso::wmrs.print.partials.meta', [
                'report' => $report,
            ])

            @include('gso::wmrs.print.partials.table', [
                'report' => $report,
                'rows' => $page['rows'] ?? [],
                'gridRows' => $gridRows,
                'lastPageGridRows' => $lastPageGridRows,
                'usedGridUnits' => (int) ($page['used_units'] ?? 0),
                'isLastPage' => $pageIndex === ($totalPages - 1),
            ])

            @if ($pageIndex === ($totalPages - 1))
                @include('gso::wmrs.print.partials.signatures', [
                    'report' => $report,
                ])

                @include('gso::wmrs.print.partials.certificate', [
                    'report' => $report,
                ])
            @endif
        </div>

        @include('gso::wmrs.print.partials.footer', [
            'pageIndex' => $pageIndex,
            'totalPages' => $totalPages,
            'footerImage' => $footerImage,
            'continuedToPage' => $totalPages > 1 && $pageIndex < ($totalPages - 1) ? ($pageIndex + 2) : null,
        ])
    </div>
@endforeach
