@include('gso::reports.regspi.print.paper.letter-landscape.pdf-styles', [
    'paperProfile' => $paperProfile,
    'pdfEngine' => 'chrome',
])