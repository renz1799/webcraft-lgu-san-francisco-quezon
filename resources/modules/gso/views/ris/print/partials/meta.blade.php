@php
    $document = $report['document'] ?? [];
@endphp

<table class="gso-ris-print-sheet">
    <colgroup>
        <col style="width: 14%;">
        <col style="width: 9%;">
        <col style="width: 43%;">
        <col style="width: 10%;">
        <col style="width: 10%;">
        <col style="width: 14%;">
    </colgroup>

    <tr class="gso-ris-print-meta-row">
        <td class="gso-ris-print-meta-label">LGU:</td>
        <td colspan="2" class="gso-ris-print-meta-value gso-ris-print-bold gso-ris-print-upper">
            {{ $document['entity_name'] ?? 'Local Government Unit' }}
        </td>
        <td class="gso-ris-print-meta-label">Fund:</td>
        <td colspan="2">{{ $document['fund'] ?? '' }}</td>
    </tr>

    <tr class="gso-ris-print-meta-row">
        <td class="gso-ris-print-meta-label">Division:</td>
        <td colspan="2">{{ $document['division'] ?? '' }}</td>
        <td class="gso-ris-print-meta-label">FPP Code:</td>
        <td colspan="2">{{ $document['fpp_code'] ?? '' }}</td>
    </tr>

    <tr class="gso-ris-print-meta-row">
        <td class="gso-ris-print-meta-label">Office:</td>
        <td colspan="2">{{ $document['office'] ?? '' }}</td>
        <td class="gso-ris-print-meta-label">RIS No.:</td>
        <td colspan="2">
            {{ $document['ris_no'] ?? '' }}
            @if (!empty($document['ris_date_label']))
                <span class="gso-ris-print-small" style="margin-left: 8px;">{{ $document['ris_date_label'] }}</span>
            @endif
        </td>
    </tr>
</table>
