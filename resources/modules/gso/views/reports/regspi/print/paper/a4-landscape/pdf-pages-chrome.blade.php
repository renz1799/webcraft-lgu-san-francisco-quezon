@include('gso::reports.regspi.print.paper.a4-landscape.pages', [
    'report' => $report,
    'paperProfile' => $paperProfile,
    'headerImage' => $headerImage ?? null,
    'footerImage' => $footerImage ?? null,
    'pdfEngine' => 'chrome',
])