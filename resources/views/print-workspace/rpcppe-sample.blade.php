<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RPCPPE Preview Sample</title>
  <x-print.workspace-styles />
  @include('print-workspace.partials.rpcppe-sample-styles')
</head>
<body class="print-workspace-body">
<x-print.workspace
  sidebar-width="clamp(320px, calc(297mm * 0.30), 390px)"
  preview-width="min(294mm, calc(100vw - clamp(320px, calc(297mm * 0.30), 390px) - 160px))"
  gap="42px"
>
  <x-slot:sidebar>
    @include('print-workspace.partials.rpcppe-sample-controls', [
      'report' => $report,
      'available_funds' => $available_funds ?? [],
      'available_departments' => $available_departments ?? [],
      'available_accountable_officers' => $available_accountable_officers ?? [],
      'rows' => $rows ?? [],
    ])
  </x-slot:sidebar>

  @include('print-workspace.partials.rpcppe-sample-pages', [
    'report' => $report,
    'rows' => $rows ?? [],
  ])
</x-print.workspace>
</body>
</html>
