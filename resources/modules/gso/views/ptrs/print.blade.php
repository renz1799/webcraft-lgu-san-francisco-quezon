<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PTR Print - {{ $print['ptr_no'] ?? $ptr->id }}</title>

  @include('gso::pars.partials.print-styles')
  <style>
    :root {
      --content-pad-bottom: -10mm;
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
    .meta-table th {
      font-size: 10.5px;
    }

    .type-option {
      display: inline-block;
      min-width: 32%;
      margin: 1mm 0;
      white-space: nowrap;
    }

    .reason-lines .line {
      border-bottom: 1px solid #000;
      height: 6mm;
    }

    .sig-rowlabel {
      font-weight: 700;
      white-space: nowrap;
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
  $maxGridRows = 12;
@endphp

@foreach(($pages ?? [[]]) as $pageIndex => $pageItems)
  @php
    $pageNo = $pageIndex + 1;
    $total = $totalPages ?? (count($pages ?? []) ?: 1);
    $isLastPage = ($pageNo === $total);
  @endphp

  <div class="page print-page">
    @include('gso::air.partials.print-header')
    @include('gso::air.partials.print-footer')

    <div class="content-wrap">
      @if($pageNo === 1)
        @include('gso::air.partials.print-controls')
      @endif

      <div class="appendix">{{ $print['appendix'] ?? 'Appendix 76' }}</div>
      <div class="title">PROPERTY TRANSFER REPORT</div>

      @include('gso::ptrs.partials.print-meta', ['ptr' => $ptr, 'print' => $print])

      @include('gso::ptrs.partials.print-items', [
        'pageItems' => $pageItems,
        'maxGridRows' => $maxGridRows,
      ])

      @if($isLastPage)
        @include('gso::ptrs.partials.print-reason', ['ptr' => $ptr, 'print' => $print])
        @include('gso::ptrs.partials.print-signatures', ['ptr' => $ptr])
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
