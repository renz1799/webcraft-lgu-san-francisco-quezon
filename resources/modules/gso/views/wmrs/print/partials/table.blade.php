@php
    $rows = collect($rows ?? [])->values();
    $gridRows = max((int) ($gridRows ?? 10), $rows->count());
    $isLastPage = (bool) ($isLastPage ?? false);
    $lastPageGridRows = max(0, (int) ($lastPageGridRows ?? 0));
    $usedGridUnits = max($rows->count(), (int) ($usedGridUnits ?? $rows->count()));
    $remainingRows = max(0, $gridRows - $usedGridUnits);

    if ($isLastPage && $lastPageGridRows > 0) {
        $remainingRows = max(0, $lastPageGridRows - $usedGridUnits);
    }

    $document = $report['document'] ?? [];
@endphp

<table class="gso-wmr-print-sheet gso-wmr-print-stack-next">
    <colgroup>
        <col style="width: 8%;">
        <col style="width: 12%;">
        <col style="width: 12%;">
        <col style="width: 28%;">
        <col style="width: 14%;">
        <col style="width: 13%;">
        <col style="width: 13%;">
    </colgroup>
    <thead class="gso-wmr-print-items-head">
        <tr>
            <th rowspan="2">Item</th>
            <th rowspan="2">Qty</th>
            <th rowspan="2">Unit</th>
            <th rowspan="2">Description</th>
            <th colspan="3">Record of Sales<br>Official Receipt</th>
        </tr>
        <tr>
            <th>No.</th>
            <th>Date</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @if ($rows->isEmpty())
            <tr>
                <td colspan="7" class="gso-wmr-print-empty-note">No WMR disposal lines are available for print yet.</td>
            </tr>
            @php $remainingRows = max(0, $remainingRows - 1); @endphp
        @else
            @foreach ($rows as $row)
                <tr class="gso-wmr-print-items-row">
                    <td class="gso-wmr-print-center">{{ (int) ($row['line_no'] ?? 0) > 0 ? (int) $row['line_no'] : ' ' }}</td>
                    <td class="gso-wmr-print-center">{{ (int) ($row['quantity'] ?? 0) > 0 ? (int) $row['quantity'] : ' ' }}</td>
                    <td class="gso-wmr-print-center">{{ $row['unit'] ?: ' ' }}</td>
                    <td>
                        @if (!empty($row['item_name']))
                            <span class="gso-wmr-print-description-title">{{ $row['item_name'] }}</span>
                        @endif
                        @if (!empty($row['description_detail']))
                            <span class="gso-wmr-print-description-detail">{{ $row['description_detail'] }}</span>
                        @elseif (empty($row['item_name']))
                            <span class="gso-wmr-print-description-detail">{{ trim((string) ($row['print_description'] ?? '')) ?: ' ' }}</span>
                        @endif
                    </td>
                    <td class="gso-wmr-print-center">{{ $row['receipt_no'] ?: ' ' }}</td>
                    <td class="gso-wmr-print-center">{{ $row['receipt_date_label'] ?: ' ' }}</td>
                    <td class="gso-wmr-print-right">
                        {{ $row['amount'] !== null ? number_format((float) $row['amount'], 2) : ' ' }}
                    </td>
                </tr>
            @endforeach
        @endif

        @for ($i = 0; $i < $remainingRows; $i++)
            <tr class="gso-wmr-print-items-row">
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        @endfor

        @if ($isLastPage)
            <tr class="gso-wmr-print-total-row">
                <td colspan="6" class="gso-wmr-print-right">TOTAL</td>
                <td class="gso-wmr-print-right">{{ number_format((float) ($document['summary']['amount_total'] ?? 0), 2) }}</td>
            </tr>
        @endif
    </tbody>
</table>
