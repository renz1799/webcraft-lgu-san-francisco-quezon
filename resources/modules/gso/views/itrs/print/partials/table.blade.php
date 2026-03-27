@php
    $rows = collect($rows ?? [])->values();
    $gridRows = max((int) ($gridRows ?? 12), $rows->count());
    $isLastPage = (bool) ($isLastPage ?? false);
    $lastPageGridRows = max(0, (int) ($lastPageGridRows ?? 0));
    $usedGridUnits = max($rows->count(), (int) ($usedGridUnits ?? $rows->count()));
    $remainingRows = max(0, $gridRows - $usedGridUnits);

    if ($isLastPage && $lastPageGridRows > 0) {
        $remainingRows = max(0, $lastPageGridRows - $usedGridUnits);
    }
@endphp

<table class="gso-ptr-print-sheet gso-ptr-print-stack-next">
    <colgroup>
        <col style="width: 7%;">
        <col style="width: 13%;">
        <col style="width: 16%;">
        <col style="width: 32%;">
        <col style="width: 12%;">
        <col style="width: 9%;">
        <col style="width: 11%;">
    </colgroup>
    <thead class="gso-ptr-print-items-head">
        <tr>
            <th>Qty</th>
            <th>Date Acquired</th>
            <th>Inventory No.</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Useful Life</th>
            <th>Condition</th>
        </tr>
    </thead>
    <tbody>
        @if ($rows->isEmpty())
            <tr>
                <td colspan="7" class="gso-ptr-print-empty-note">No ITR line items are available for print yet.</td>
            </tr>
            @php $remainingRows = max(0, $remainingRows - 1); @endphp
        @else
            @foreach ($rows as $row)
                <tr class="gso-ptr-print-items-row">
                    <td class="gso-ptr-print-center">{{ (int) ($row['quantity'] ?? 0) }}</td>
                    <td class="gso-ptr-print-center">{{ $row['date_acquired_label'] ?: ' ' }}</td>
                    <td class="gso-ptr-print-center">{{ $row['inventory_item_no'] ?: ' ' }}</td>
                    <td>{{ $row['description'] ?: ' ' }}</td>
                    <td class="gso-ptr-print-right">
                        {{ $row['amount'] !== null ? number_format((float) $row['amount'], 2) : ' ' }}
                    </td>
                    <td class="gso-ptr-print-center">{{ $row['estimated_useful_life'] ?: ' ' }}</td>
                    <td class="gso-ptr-print-center">{{ $row['condition'] ?: ' ' }}</td>
                </tr>
            @endforeach
        @endif

        @for ($i = 0; $i < $remainingRows; $i++)
            <tr class="gso-ptr-print-items-row">
                <td>&nbsp;</td>
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
