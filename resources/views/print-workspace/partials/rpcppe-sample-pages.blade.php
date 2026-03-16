@php
  $rowsPerPage = 12;
  $blankRowCutoff = 8;
  $pages = collect($rows ?? [])->chunk($rowsPerPage)->values();
  if ($pages->isEmpty()) {
      $pages = collect([collect()]);
  }
@endphp

@foreach($pages as $pageIndex => $pageRows)
  @php
    $pageNo = $pageIndex + 1;
    $totalPages = $pages->count();
    $isLastPage = $pageNo === $totalPages;
  @endphp

  <div class="rpcppe-sample-page">
    <div class="rpcppe-sample-content-frame">
      <div class="rpcppe-sample-content">
        <div class="rpcppe-sample-appendix">{{ $report['appendix_label'] ?? 'RPCPPE' }}</div>
        <div class="rpcppe-sample-title">REPORT ON THE PHYSICAL COUNT OF PROPERTY, PLANT AND EQUIPMENT</div>

        @include('print-workspace.partials.rpcppe-sample-meta', ['report' => $report])
        @include('print-workspace.partials.rpcppe-sample-items', [
          'pageRows' => $pageRows,
          'maxGridRows' => $rowsPerPage,
          'blankRowCutoff' => $blankRowCutoff,
        ])

        @if($isLastPage)
          @include('print-workspace.partials.rpcppe-sample-signatures', ['report' => $report])
        @endif
      </div>
    </div>

    <div class="rpcppe-sample-page-number">Page {{ $pageNo }} of {{ $totalPages }}</div>
  </div>
@endforeach
