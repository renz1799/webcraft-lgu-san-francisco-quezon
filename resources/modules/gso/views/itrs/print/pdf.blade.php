<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] ?? 'Inventory Transfer Report' }}</title>

    @php
        $resolvedPdfEngine = ($pdfEngine ?? null) === 'dompdf' ? 'dompdf' : 'chrome';
        $defaultPdfStylesView = $paperProfile['pdf_styles_view'] ?? $paperProfile['styles_view'];
        $defaultPdfPagesView = $paperProfile['pages_view'];
        $derivedPdfStylesView = str_replace('.pdf-styles', '.pdf-styles-' . $resolvedPdfEngine, $defaultPdfStylesView);
        $derivedPdfPagesView = str_replace('.pages', '.pdf-pages-' . $resolvedPdfEngine, $defaultPdfPagesView);
        $pdfStylesView = $paperProfile[$resolvedPdfEngine . '_pdf_styles_view']
            ?? (\Illuminate\Support\Facades\View::exists($derivedPdfStylesView) ? $derivedPdfStylesView : $defaultPdfStylesView);
        $pdfPagesView = $paperProfile[$resolvedPdfEngine . '_pdf_pages_view']
            ?? (\Illuminate\Support\Facades\View::exists($derivedPdfPagesView) ? $derivedPdfPagesView : $defaultPdfPagesView);
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
        'headerImage' => public_path($paperProfile['header_image_pdf']),
        'footerImage' => public_path($paperProfile['footer_image_pdf']),
        'pdfEngine' => $resolvedPdfEngine,
    ])
</body>
</html>
