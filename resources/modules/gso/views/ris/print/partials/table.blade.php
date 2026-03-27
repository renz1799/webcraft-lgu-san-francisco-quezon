@php
    $document = $report['document'] ?? [];
    $maxRows = $gridRows ?? 24;
    $pageRows = $rows ?? [];
    $usedGridUnits = max(count($pageRows), (int) ($usedGridUnits ?? count($pageRows)));
    $lastPageGridRows = max(0, (int) ($lastPageGridRows ?? 0));
    $isLastPage = (bool) ($isLastPage ?? false);
    $blankRows = $isLastPage && $lastPageGridRows > 0
        ? max(0, $lastPageGridRows - $usedGridUnits)
        : max(0, $maxRows - $usedGridUnits);
@endphp

<table class="gso-ris-print-sheet gso-ris-print-stack-next">
    <colgroup>
        <col style="width: 14%;">
        <col style="width: 9%;">
        <col style="width: 43%;">
        <col style="width: 10%;">
        <col style="width: 10%;">
        <col style="width: 14%;">
    </colgroup>

    <thead class="gso-ris-print-items-head">
        <tr>
            <th>Stock No.</th>
            <th>Unit</th>
            <th>Description</th>
            <th>Quantity<br><span class="gso-ris-print-small">(Requisition)</span></th>
            <th>Quantity<br><span class="gso-ris-print-small">(Issuance)</span></th>
            <th>Remarks</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($pageRows as $row)
            <tr class="gso-ris-print-items-row">
                <td class="gso-ris-print-center">{{ $row['stock_no'] ?: ' ' }}</td>
                <td class="gso-ris-print-center">{{ $row['unit'] ?: ' ' }}</td>
                <td>{{ $row['description'] ?: ' ' }}</td>
                <td class="gso-ris-print-center">{{ $row['qty_requested'] ?? ' ' }}</td>
                <td class="gso-ris-print-center">{{ $row['qty_issued'] ?? ' ' }}</td>
                <td>{{ $row['remarks'] ?: ' ' }}</td>
            </tr>
        @endforeach

        @for ($i = 0; $i < $blankRows; $i++)
            <tr class="gso-ris-print-items-row">
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        @endfor
    </tbody>
</table>

<table class="gso-ris-print-sheet gso-ris-print-stack-next">
    <colgroup>
        <col style="width: 14%;">
        <col style="width: 86%;">
    </colgroup>
    <tr>
        <td class="gso-ris-print-bold">Purpose:</td>
        <td style="white-space: pre-wrap;">{{ $document['purpose'] ?: ' ' }}</td>
    </tr>
</table>
