@include('gso::reports.rspi.print.paper.letter-landscape.pages', [
    'report' => $report,
    'paperProfile' => $paperProfile,
    'headerImage' => $headerImage ?? null,
    'footerImage' => $footerImage ?? null,
    'pdfEngine' => 'dompdf',
])