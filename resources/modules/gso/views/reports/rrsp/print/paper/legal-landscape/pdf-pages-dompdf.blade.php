@include('gso::reports.rrsp.print.paper.legal-landscape.pages', [
    'report' => $report,
    'paperProfile' => $paperProfile,
    'headerImage' => $headerImage ?? null,
    'footerImage' => $footerImage ?? null,
    'pdfEngine' => 'dompdf',
])