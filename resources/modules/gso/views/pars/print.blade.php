<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PAR Print - {{ $par->par_number ?? $par->id }}</title>

  @include('gso::pars.partials.print-styles')
</head>

<body>
@php
  // fixed grid rows per page (keeps the table height fixed)
  $maxGridRows = 26;

  // items per page; should MATCH service chunk size
  $perPage = $perPage ?? 26;
@endphp

@foreach(($pages ?? [[]]) as $pageIndex => $pageItems)
  @php
    $pageNo = $pageIndex + 1;
    $total = $totalPages ?? (count($pages ?? []) ?: 1);
    $isLastPage = ($pageNo === $total);
  @endphp

  <div class="page print-page">
    @include('gso::pars.partials.print-header')
    @include('gso::pars.partials.print-footer')

    <div class="content-wrap">
      @if($pageNo === 1)
        @include('gso::pars.partials.print-controls')
      @endif

      <div class="appendix">Appendix 71</div>
      <div class="title">PROPERTY ACKNOWLEDGEMENT RECEIPT</div>

      @include('gso::pars.partials.print-meta', ['par' => $par, 'print' => $print])

      @include('gso::pars.partials.print-items', [
        'pageItems' => $pageItems,
        'maxGridRows' => $maxGridRows,
      ])

      @if($isLastPage)
        @include('gso::pars.partials.print-signatures', ['par' => $par])
      @endif

      @if($total > 1)
        <div class="small right" style="margin-top:2mm;">
          Page {{ $pageNo }} of {{ $total }}
        </div>
      @endif
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
