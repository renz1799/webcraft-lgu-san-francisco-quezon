@php
    $allRows = array_values($report['rows'] ?? []);
    $rowsPerPage = max(1, (int) ($paperProfile['rows_per_page'] ?? 24));
    $firstPageRows = max(1, (int) ($paperProfile['first_page_rows'] ?? $rowsPerPage));
    $laterPageRows = max(1, (int) ($paperProfile['later_page_rows'] ?? $rowsPerPage));
    $gridRows = max($rowsPerPage, (int) ($paperProfile['grid_rows'] ?? ($report['max_grid_rows'] ?? 24)));
    $pages = [];
    $cursor = 0;
    $pageNumber = 0;

    if ($allRows === []) {
        $pages = [[]];
    } else {
        while ($cursor < count($allRows)) {
            $capacity = $pageNumber === 0 ? $firstPageRows : $laterPageRows;
            $pages[] = array_slice($allRows, $cursor, $capacity);
            $cursor += $capacity;
            $pageNumber++;
        }
    }

    $totalPages = count($pages);
@endphp

@foreach ($pages as $pageIndex => $pageRows)
    <div class="gso-air-print-page">
        @include('gso::air.print.partials.header', [
            'report' => $report,
            'headerImage' => $headerImage,
            'continuationFromPage' => $totalPages > 1 && $pageIndex > 0 ? $pageIndex : null,
        ])

        <div class="gso-air-print-body">
            @include('gso::air.print.partials.table', [
                'report' => $report,
                'rows' => $pageRows,
                'gridRows' => $gridRows,
                'pageIndex' => $pageIndex,
                'totalPages' => $totalPages,
                'fillRows' => $pageIndex < ($totalPages - 1),
                'lastPageGridRows' => (int) ($paperProfile['last_page_grid_rows'] ?? 0),
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
