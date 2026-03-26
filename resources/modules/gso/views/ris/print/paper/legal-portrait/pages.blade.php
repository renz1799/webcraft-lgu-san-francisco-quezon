@php
    $rowsPerPage = max(1, (int) ($paperProfile['rows_per_page'] ?? 30));
    $gridRows = max($rowsPerPage, (int) ($paperProfile['grid_rows'] ?? 30));
    $rawPages = array_chunk($report['rows'] ?? [], $rowsPerPage);
    $rawPages = $rawPages === [] ? [[]] : $rawPages;
    $totalPages = count($rawPages);
@endphp

@foreach ($rawPages as $pageIndex => $pageRows)
    <div class="gso-ris-print-page">
        @include('gso::ris.print.partials.header', [
            'report' => $report,
            'headerImage' => $headerImage,
        ])

        <div class="gso-ris-print-body">
            @include('gso::ris.print.partials.meta', [
                'report' => $report,
            ])

            @include('gso::ris.print.partials.table', [
                'report' => $report,
                'rows' => $pageRows,
                'gridRows' => $gridRows,
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
        ])
    </div>
@endforeach
