@include('gso::reports.rpci.print.paper.a4-landscape.pdf-pages-dompdf', [
    'report' => $report,
    'paperProfile' => $paperProfile,
    'headerImage' => $headerImage ?? null,
    'footerImage' => $footerImage ?? null,
])
