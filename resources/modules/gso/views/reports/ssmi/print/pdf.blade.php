<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] ?? 'Summary of Supplies and Materials Issued' }}</title>

    @include($paperProfile['pdf_styles_view'], [
        'paperProfile' => $paperProfile,
    ])
</head>
<body>
    @include($paperProfile['pages_view'], [
        'report' => $report,
        'paperProfile' => $paperProfile,
        'headerImage' => !empty($paperProfile['header_image_pdf']) ? public_path($paperProfile['header_image_pdf']) : null,
        'footerImage' => !empty($paperProfile['footer_image_pdf']) ? public_path($paperProfile['footer_image_pdf']) : null,
    ])
</body>
</html>
