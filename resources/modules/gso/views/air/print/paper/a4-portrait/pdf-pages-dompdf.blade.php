@include('gso::air.print.paper.a4-portrait.pages', [
    'report' => $report,
    'paperProfile' => $paperProfile,
    'headerImage' => $headerImage,
    'footerImage' => $footerImage,
    'pdfEngine' => $pdfEngine ?? 'dompdf',
])
