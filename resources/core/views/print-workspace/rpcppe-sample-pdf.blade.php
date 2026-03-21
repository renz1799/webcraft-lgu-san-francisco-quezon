<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RPCPPE Preview PDF</title>
  @include('print-workspace.partials.rpcppe-sample-pdf-styles', [
    'assetUrls' => $assetUrls ?? [],
  ])
</head>
<body class="rpcppe-pdf-body">
  <main class="rpcppe-pdf-document">
    @include('print-workspace.partials.rpcppe-sample-pages', [
      'report' => $report,
      'rows' => $rows ?? [],
    ])
  </main>
</body>
</html>
