@include('gso::reports.ssmi.print.paper.letter-landscape.pdf-styles', [
    'paperProfile' => $paperProfile,
    'pdfEngine' => 'chrome',
])