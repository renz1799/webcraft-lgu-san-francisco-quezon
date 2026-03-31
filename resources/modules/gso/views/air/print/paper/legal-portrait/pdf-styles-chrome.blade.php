@include('gso::air.print.paper.legal-portrait.pdf-styles', [
    'paperProfile' => $paperProfile,
    'pdfEngine' => $pdfEngine ?? 'chrome',
])
