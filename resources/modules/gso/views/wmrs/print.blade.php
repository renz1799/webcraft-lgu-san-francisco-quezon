<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>WMR Print - {{ $print['wmr_no'] ?? $wmr->id }}</title>

  @include('pars.partials.print-styles')
  <style>
    :root {
      --content-pad-bottom: -8mm;
    }

    .print-page {
      break-after: page;
      page-break-after: always;
    }

    .print-page:last-of-type {
      break-after: auto !important;
      page-break-after: auto !important;
    }

    .meta-table td,
    .meta-table th,
    .certificate-table td,
    .certificate-table th,
    .sign-table td,
    .sign-table th {
      font-size: 10.5px;
    }

    .sig-cell {
      padding: 1.5mm 2mm !important;
      vertical-align: middle !important;
    }

    .sig-value {
      min-height: 14px;
      font-weight: 700;
      text-align: center;
      border-bottom: 1px solid #000;
      padding-bottom: 2px;
      text-transform: uppercase;
    }
  </style>
</head>
<body>
@php
  $maxGridRows = 10;
@endphp

@foreach(($pages ?? [[]]) as $pageIndex => $pageItems)
  @php
    $pageNo = $pageIndex + 1;
    $total = $totalPages ?? (count($pages ?? []) ?: 1);
    $isLastPage = ($pageNo === $total);
  @endphp

  <div class="page print-page">
    @include('air.partials.print-header')
    @include('air.partials.print-footer')

    <div class="content-wrap">
      @if($pageNo === 1)
        @include('air.partials.print-controls')
      @endif

      @if(!empty($print['appendix']))
        <div class="appendix">{{ $print['appendix'] }}</div>
      @endif
      <div class="title">WASTE MATERIALS REPORT</div>

      @include('wmr.partials.print-meta', ['wmr' => $wmr, 'print' => $print])

      @include('wmr.partials.print-items', [
        'pageItems' => $pageItems,
        'maxGridRows' => $maxGridRows,
        'showTotal' => $isLastPage,
        'totalAmount' => $print['total_amount'] ?? 0,
      ])

      @if($isLastPage)
        @include('wmr.partials.print-signatures', ['wmr' => $wmr])
        @include('wmr.partials.print-certificate', ['wmr' => $wmr, 'print' => $print])
      @endif

      <div class="small right" style="margin-top:2mm;">
        Page {{ $pageNo }} of {{ $total }}
      </div>
    </div>
  </div>
@endforeach

<script>
  window.addEventListener('load', () => {
    window.print();
  });
</script>
</body>
</html>