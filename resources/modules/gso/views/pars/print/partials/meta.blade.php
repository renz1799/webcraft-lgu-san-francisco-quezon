@php
    $document = $report['document'] ?? [];
@endphp

<table class="gso-par-print-sheet">
    <colgroup>
        <col style="width:70%;">
        <col style="width:30%;">
    </colgroup>
    <tbody>
        <tr class="gso-par-print-meta-row">
            <td>
                <span class="gso-par-print-meta-label">Entity Name :</span>
                <span class="gso-par-print-meta-value">{{ $document['entity_name'] ?? '' }}</span>
            </td>
            <td>
                <span class="gso-par-print-meta-label">Date :</span>
                <span class="gso-par-print-meta-value">{{ $document['issued_date_label'] ?? '' }}</span>
            </td>
        </tr>
        <tr class="gso-par-print-meta-row">
            <td>
                <span class="gso-par-print-meta-label">Fund Cluster :</span>
                <span class="gso-par-print-meta-value">{{ $document['fund_cluster'] ?? '' }}</span>
            </td>
            <td>
                <span class="gso-par-print-meta-label">PAR No. :</span>
                <span class="gso-par-print-meta-value">{{ $document['par_number'] ?? '' }}</span>
            </td>
        </tr>
    </tbody>
</table>
