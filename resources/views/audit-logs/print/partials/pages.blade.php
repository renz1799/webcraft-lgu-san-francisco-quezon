@php
    $rowsPerPage = 15;
    $pages = array_chunk($report->rows, $rowsPerPage);
    $totalPages = count($pages);
@endphp

@foreach ($pages as $pageIndex => $pageRows)
    <div class="audit-print-page">

        @include('audit-logs.print.partials.header', [
            'headerImage' => $headerImage,
        ])

        <div class="audit-print-body">

            @if ($pageIndex === 0)
                @include('audit-logs.print.partials.meta', ['report' => $report])
            @else
                <h1 class="audit-print-title">{{ $report->title }}</h1>
            @endif

            @include('audit-logs.print.partials.table', [
                'rows' => $pageRows,
            ])

        </div>

        @include('audit-logs.print.partials.footer', [
            'footerImage' => $footerImage,
            'pageIndex' => $pageIndex,
            'totalPages' => $totalPages,
        ])

    </div>
@endforeach