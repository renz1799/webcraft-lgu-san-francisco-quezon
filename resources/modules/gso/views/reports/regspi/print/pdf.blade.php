<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] ?? 'Register of Semi-Expendable Property Issued' }}</title>

    @php
        $resolvedPdfEngine = ($pdfEngine ?? null) === 'dompdf' ? 'dompdf' : 'chrome';
        $defaultPdfStylesView = $paperProfile['pdf_styles_view'] ?? $paperProfile['styles_view'];
        $defaultPdfPagesView = $paperProfile['pages_view'];
        $derivedPdfStylesView = str_replace('.pdf-styles', '.pdf-styles-' . $resolvedPdfEngine, $defaultPdfStylesView);
        $derivedPdfPagesView = str_replace('.pages', '.pdf-pages-' . $resolvedPdfEngine, $defaultPdfPagesView);
        $pdfStylesView = $paperProfile[$resolvedPdfEngine . '_pdf_styles_view'] ?? $derivedPdfStylesView;
        $pdfPagesView = $paperProfile[$resolvedPdfEngine . '_pdf_pages_view'] ?? $derivedPdfPagesView;
    @endphp

    @include($pdfStylesView, [
        'paperProfile' => $paperProfile,
        'pdfEngine' => $resolvedPdfEngine,
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
