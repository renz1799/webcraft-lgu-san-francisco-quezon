@include('gso::reports.rpcsp.print.paper.a4-landscape.pdf-styles', [
    'paperProfile' => $paperProfile,
    'pdfEngine' => 'dompdf',
])