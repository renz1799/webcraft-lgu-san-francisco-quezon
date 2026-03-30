@include('gso::reports.rpcsp.print.paper.legal-landscape.pdf-styles', [
    'paperProfile' => $paperProfile,
    'pdfEngine' => 'dompdf',
])