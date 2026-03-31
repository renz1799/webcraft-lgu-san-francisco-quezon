@include('gso::air.print.paper.legal-portrait.pages', [
    'report' => $report,
    'paperProfile' => $paperProfile,
    'headerImage' => $headerImage,
    'footerImage' => $footerImage,
    'pdfEngine' => $pdfEngine ?? 'chrome',
])
