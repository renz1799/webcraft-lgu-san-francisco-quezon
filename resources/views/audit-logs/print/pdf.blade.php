<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report->title }}</title>
    @include('audit-logs.print.partials.pdf-styles')
</head>
<body>
    @include('audit-logs.print.partials.pages', [
        'report' => $report,
        'headerImage' => public_path('headers/a4_header_template_dark_2480x300.png'),
        'footerImage' => public_path('headers/a4_footer_template_dark_2480x250.png'),
    ])
</body>
</html>