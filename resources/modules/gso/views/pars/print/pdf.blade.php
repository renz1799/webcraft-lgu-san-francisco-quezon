<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] ?? 'Property Acknowledgement Receipt' }}</title>

    @include($paperProfile['pdf_styles_view'], [
        'paperProfile' => $paperProfile,
    ])
</head>
<body>
    @include($paperProfile['pages_view'], [
        'report' => $report,
        'paperProfile' => $paperProfile,
        'headerImage' => public_path($paperProfile['header_image_pdf']),
        'footerImage' => public_path($paperProfile['footer_image_pdf']),
    ])
</body>
</html>
