@php
    $rowsPrinted = 0;
    $maxRows = $maxGridRows ?? 28;
    $rows = $pageItems ?? [];
@endphp

<table class="items-table stack-next">
    <colgroup>
        <col style="width: 14.75%;">
        <col style="width: 9%;">
        <col style="width: 44%;">
        <col style="width: 11%;">
        <col style="width: 10%;">
        <col style="width: 14%;">
    </colgroup>

    <thead class="items-head">
        <tr>
            <th>Stock No.</th>
            <th>Unit</th>
            <th>Description</th>
            <th>Quantity<br><span class="small">(Requisition)</span></th>
            <th>Quantity<br><span class="small">(Issuance)</span></th>
            <th>Remarks</th>
        </tr>
    </thead>

    <tbody>
        @foreach($rows as $row)
            @php $rowsPrinted++; @endphp
            <tr class="items-row">
                <td class="center">{{ $row['stock_no'] ?: ' ' }}</td>
                <td class="center">{{ $row['unit'] ?: ' ' }}</td>
                <td>{{ $row['description'] ?: ' ' }}</td>
                <td class="center">{{ $row['qty_requested'] ?? ' ' }}</td>
                <td class="center">{{ $row['qty_issued'] ?? ' ' }}</td>
                <td>{{ $row['remarks'] ?: ' ' }}</td>
            </tr>
        @endforeach

        @for($i = $rowsPrinted; $i < $maxRows; $i++)
            <tr class="items-row">
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

<table class="footer-table stack-next">
    <colgroup>
        <col style="width: 14.35%;">
        <col style="width: 85%;">
    </colgroup>
    <tr>
        <td class="bold">Purpose:</td>
        <td style="white-space: pre-wrap;">{{ $ris->purpose ?? ' ' }}</td>
    </tr>
</table>
