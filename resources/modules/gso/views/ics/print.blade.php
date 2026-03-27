<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ICS Print - {{ $print['ics_no'] ?? $ics->id }}</title>

    @include('gso::pars.partials.print-styles')
    <style>
        :root {
            --content-pad-bottom: -12mm;
        }

        .print-page {
            break-after: page;
            page-break-after: always;
        }

        .print-page:last-of-type {
            break-after: auto !important;
            page-break-after: auto !important;
        }
    </style>
</head>

<body>
@php
    $maxGridRows = 28;
@endphp

@foreach(($pages ?? [[]]) as $pageIndex => $pageItems)
    @php
        $pageNo = $pageIndex + 1;
        $total = $totalPages ?? count($pages ?? []) ?: 1;
        $isLastPage = ($pageNo === $total);
    @endphp

    <div class="page print-page">
        @include('gso::pars.partials.print-header')
        @include('gso::pars.partials.print-footer')

        <div class="content-wrap">
            @if($pageNo === 1)
                @include('gso::pars.partials.print-controls')
            @endif

            <div class="appendix">{{ $print['appendix'] ?? 'Appendix 59' }}</div>
            <div class="title">INVENTORY CUSTODIAN SLIP</div>

            @include('gso::ics.partials.print-meta', ['ics' => $ics, 'print' => $print])

            @include('gso::ics.partials.print-items', [
                'pageItems' => $pageItems,
                'maxGridRows' => $maxGridRows,
            ])

            @if($isLastPage)
                @include('gso::ics.partials.print-signatures', ['ics' => $ics])
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
