<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] ?? 'Property Cards' }}</title>

    @include($paperProfile['pdf_styles_view'], [
        'paperProfile' => $paperProfile,
    ])
</head>
<body>
    @include($paperProfile['pages_view'], [
        'report' => $report,
        'paperProfile' => $paperProfile,
    ])
</body>
</html>
