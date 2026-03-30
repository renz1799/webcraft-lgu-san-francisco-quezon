@include('gso::reports.regspi.print.paper.legal-landscape.pdf-styles', [
    'paperProfile' => $paperProfile,
    'pdfEngine' => 'dompdf',
])