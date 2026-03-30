<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] ?? 'Report on the Physical Count of Inventories' }}</title>

    @php
        $resolvedPdfEngine = ($pdfEngine ?? null) === 'dompdf' ? 'dompdf' : 'chrome';
        $pdfStylesView = $paperProfile[$resolvedPdfEngine . '_pdf_styles_view'] ?? $paperProfile['pdf_styles_view'];
        $pdfPagesView = $paperProfile[$resolvedPdfEngine . '_pdf_pages_view'] ?? $paperProfile['pages_view'];
    @endphp

    @include($pdfStylesView, [
        'paperProfile' => $paperProfile,
    ])
</head>
<body>
    @include($pdfPagesView, [
        'report' => $report,
        'paperProfile' => $paperProfile,
        'headerImage' => !empty($paperProfile['header_image_pdf']) ? public_path($paperProfile['header_image_pdf']) : null,
        'footerImage' => !empty($paperProfile['footer_image_pdf']) ? public_path($paperProfile['footer_image_pdf']) : null,
        'pdfEngine' => $resolvedPdfEngine,
    ])
</body>
</html>
