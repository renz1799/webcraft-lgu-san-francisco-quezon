@php
    $document = $report['document'] ?? [];
    $maxRows = $gridRows ?? 24;
    $pageRows = $rows ?? [];
    $itemColumns = array_merge([
        'stock_no' => '14%',
        'unit' => '9%',
        'description' => '43%',
        'qty_requested' => '10%',
        'qty_issued' => '10%',
        'remarks' => '14%',
    ], (array) ($paperProfile['item_column_widths'] ?? []));
    $usedGridUnits = max(count($pageRows), (int) ($usedGridUnits ?? count($pageRows)));
    $lastPageGridRows = max(0, (int) ($lastPageGridRows ?? 0));
    $isLastPage = (bool) ($isLastPage ?? false);
    $blankRows = $isLastPage && $lastPageGridRows > 0
        ? max(0, $lastPageGridRows - $usedGridUnits)
        : max(0, $maxRows - $usedGridUnits);
@endphp

<table class="gso-ris-print-sheet gso-ris-print-stack-next">
    <colgroup>
        <col style="width: {{ $itemColumns['stock_no'] }};">
        <col style="width: {{ $itemColumns['unit'] }};">
        <col style="width: {{ $itemColumns['description'] }};">
        <col style="width: {{ $itemColumns['qty_requested'] }};">
        <col style="width: {{ $itemColumns['qty_issued'] }};">
        <col style="width: {{ $itemColumns['remarks'] }};">
    </colgroup>

    <thead class="gso-ris-print-items-head">
        <tr>
            <th class="gso-ris-print-col--stock-no" style="width: {{ $itemColumns['stock_no'] }};">Stock No.</th>
            <th class="gso-ris-print-col--unit gso-ris-print-col-head--compact" style="width: {{ $itemColumns['unit'] }};">Unit</th>
            <th class="gso-ris-print-col--description" style="width: {{ $itemColumns['description'] }};">Description</th>
            <th class="gso-ris-print-col--qty gso-ris-print-col-head--compact" style="width: {{ $itemColumns['qty_requested'] }};">Quantity<br><span class="gso-ris-print-small">(Requisition)</span></th>
            <th class="gso-ris-print-col--qty gso-ris-print-col-head--compact" style="width: {{ $itemColumns['qty_issued'] }};">Quantity<br><span class="gso-ris-print-small">(Issuance)</span></th>
            <th class="gso-ris-print-col--remarks" style="width: {{ $itemColumns['remarks'] }};">Remarks</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($pageRows as $row)
            <tr class="gso-ris-print-items-row">
                <td class="gso-ris-print-center gso-ris-print-col--stock-no" style="width: {{ $itemColumns['stock_no'] }};">{{ $row['stock_no'] ?: ' ' }}</td>
                <td class="gso-ris-print-center gso-ris-print-col--unit gso-ris-print-col-cell--compact" style="width: {{ $itemColumns['unit'] }};">{{ $row['unit'] ?: ' ' }}</td>
                <td class="gso-ris-print-col--description" style="width: {{ $itemColumns['description'] }};">{{ $row['description'] ?: ' ' }}</td>
                <td class="gso-ris-print-center gso-ris-print-col--qty gso-ris-print-col-cell--compact gso-ris-print-cell--numeric" style="width: {{ $itemColumns['qty_requested'] }};">{{ $row['qty_requested'] ?? ' ' }}</td>
                <td class="gso-ris-print-center gso-ris-print-col--qty gso-ris-print-col-cell--compact gso-ris-print-cell--numeric" style="width: {{ $itemColumns['qty_issued'] }};">{{ $row['qty_issued'] ?? ' ' }}</td>
                <td class="gso-ris-print-col--remarks" style="width: {{ $itemColumns['remarks'] }};">{{ $row['remarks'] ?: ' ' }}</td>
            </tr>
        @endforeach

        @for ($i = 0; $i < $blankRows; $i++)
            <tr class="gso-ris-print-items-row">
                <td class="gso-ris-print-col--stock-no" style="width: {{ $itemColumns['stock_no'] }};">&nbsp;</td>
                <td class="gso-ris-print-col--unit" style="width: {{ $itemColumns['unit'] }};">&nbsp;</td>
                <td class="gso-ris-print-col--description" style="width: {{ $itemColumns['description'] }};">&nbsp;</td>
                <td class="gso-ris-print-col--qty" style="width: {{ $itemColumns['qty_requested'] }};">&nbsp;</td>
                <td class="gso-ris-print-col--qty" style="width: {{ $itemColumns['qty_issued'] }};">&nbsp;</td>
                <td class="gso-ris-print-col--remarks" style="width: {{ $itemColumns['remarks'] }};">&nbsp;</td>
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
