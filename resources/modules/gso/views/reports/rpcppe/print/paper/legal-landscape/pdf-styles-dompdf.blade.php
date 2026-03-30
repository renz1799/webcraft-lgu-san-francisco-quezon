@include('gso::reports.rpcppe.print.paper.legal-landscape.pdf-styles', [
    'paperProfile' => $paperProfile,
    'pdfEngine' => 'dompdf',
])
