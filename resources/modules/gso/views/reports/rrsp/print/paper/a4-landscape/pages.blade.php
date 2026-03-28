@php
    $pages = collect($report['pagination']['pages'] ?? []);
    if ($pages->isEmpty()) {
        $pages = collect([['rows' => [], 'used_units' => 0]]);
    }
    $totalPages = $pages->count();
@endphp

@foreach ($pages as $pageIndex => $page)
    @include('gso::reports.rrsp.print.partials.page', [
        'report' => $report,
        'paperProfile' => $paperProfile,
        'headerImage' => $headerImage ?? null,
        'footerImage' => $footerImage ?? null,
        'page' => $page,
        'pageNo' => $pageIndex + 1,
        'totalPages' => $totalPages,
    ])
@endforeach
