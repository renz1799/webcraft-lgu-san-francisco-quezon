@php
    $rowsPerPage = max(1, (int) ($paperProfile['rows_per_page'] ?? 18));
    $gridRows = max($rowsPerPage, (int) ($paperProfile['grid_rows'] ?? ($report['max_grid_rows'] ?? 20)));
    $rawPages = array_chunk($report['rows'] ?? [], $rowsPerPage);
    $rawPages = $rawPages === [] ? [[]] : $rawPages;
    $totalPages = count($rawPages);
@endphp

@foreach ($rawPages as $pageIndex => $rawPageRows)
    @php
        $pageRows = $rawPageRows;

        if ($totalPages > 1 && $pageIndex > 0) {
            array_unshift($pageRows, [
                'property_no' => '',
                'description' => '*** CONTINUATION FROM PAGE ' . $pageIndex . ' ***',
                'unit' => '',
                'quantity' => '',
                '__msg' => true,
            ]);
        }

        if ($totalPages > 1 && $pageIndex < ($totalPages - 1)) {
            $pageRows[] = [
                'property_no' => '',
                'description' => '*** CONTINUED ON PAGE ' . ($pageIndex + 2) . ' ***',
                'unit' => '',
                'quantity' => '',
                '__msg' => true,
            ];
        }
    @endphp

    <div class="gso-air-print-page">
        @include('gso::air.print.partials.header', [
            'report' => $report,
            'headerImage' => $headerImage,
        ])

        <div class="gso-air-print-body">
            @include('gso::air.print.partials.table', [
                'report' => $report,
                'rows' => $pageRows,
                'gridRows' => $gridRows,
                'pageIndex' => $pageIndex,
                'totalPages' => $totalPages,
            ])
        </div>

        @include('gso::air.print.partials.footer', [
            'report' => $report,
            'footerImage' => $footerImage,
            'pageIndex' => $pageIndex,
            'totalPages' => $totalPages,
        ])
    </div>
@endforeach
