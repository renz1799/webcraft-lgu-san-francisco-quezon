<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] ?? 'Sticker Printing' }}</title>

    @include('gso::reports.stickers.print.partials.styles', [
        'sticker' => $sticker,
        'stickerBackgroundUrl' => $stickerBackgroundUrl ?? null,
    ])
</head>
<body>
    @include('gso::reports.stickers.print.partials.sheet', [
        'sticker' => $sticker,
        'stickers' => $stickers,
        'controls' => $controls,
        'sheet' => $sheet,
        'stickerBackgroundUrl' => $stickerBackgroundUrl ?? null,
    ])
</body>
</html>
