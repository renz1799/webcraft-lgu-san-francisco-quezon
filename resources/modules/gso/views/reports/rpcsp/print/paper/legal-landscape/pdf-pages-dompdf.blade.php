@include('gso::reports.rpcsp.print.paper.legal-landscape.pages', [
    'report' => $report,
    'paperProfile' => $paperProfile,
    'headerImage' => $headerImage ?? null,
    'footerImage' => $footerImage ?? null,
    'pdfEngine' => 'dompdf',
])