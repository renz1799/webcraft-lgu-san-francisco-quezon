<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RIS Print - {{ $print['ris_no'] ?: ($ris->id ?? 'RIS') }}</title>
    @include('gso::ris.partials.print-styles')
</head>
<body>
    <div class="no-print">
        <button class="btn" type="button" onclick="window.print()">Print</button>
        <a class="btn" href="{{ route('gso.ris.edit', ['ris' => $ris->id]) }}">Back to RIS</a>
    </div>

    @foreach(($pages ?? [[]]) as $pageIndex => $pageItems)
        @php
            $pageNo = $pageIndex + 1;
            $total = $totalPages ?? count($pages ?? []) ?: 1;
            $isLastPage = ($pageNo === $total);
        @endphp

        <div class="page print-page">
            <div class="appendix">Appendix 48</div>
            <div class="title">REQUISITION AND ISSUE SLIP</div>

            @include('gso::ris.partials.print-meta', ['ris' => $ris, 'print' => $print])
            @include('gso::ris.partials.print-items', [
                'ris' => $ris,
                'pageItems' => $pageItems,
                'maxGridRows' => 28,
            ])

            @if($isLastPage)
                @include('gso::ris.partials.print-signatures', ['ris' => $ris])
            @endif

            <div class="small right" style="margin-top: 6mm;">
                Page {{ $pageNo }} of {{ $total }}
            </div>
        </div>
    @endforeach
</body>
</html>
