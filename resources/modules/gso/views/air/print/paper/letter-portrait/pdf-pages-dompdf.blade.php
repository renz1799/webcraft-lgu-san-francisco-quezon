@include('gso::air.print.paper.letter-portrait.pages', [
    'report' => $report,
    'paperProfile' => $paperProfile,
    'headerImage' => $headerImage,
    'footerImage' => $footerImage,
    'pdfEngine' => $pdfEngine ?? 'dompdf',
])
