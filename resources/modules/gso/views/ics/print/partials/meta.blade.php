@php
    $document = $report['document'] ?? [];
@endphp

<table class="gso-ics-print-sheet">
    <colgroup>
        <col style="width:70%;">
        <col style="width:30%;">
    </colgroup>
    <tbody>
        <tr class="gso-ics-print-meta-row">
            <td>
                <span class="gso-ics-print-meta-label">Entity Name :</span>
                <span class="gso-ics-print-meta-value">{{ $document['entity_name'] ?? '' }}</span>
            </td>
            <td>
                <span class="gso-ics-print-meta-label">ICS No. :</span>
                <span class="gso-ics-print-meta-value">{{ $document['ics_no'] ?? '' }}</span>
            </td>
        </tr>
        <tr class="gso-ics-print-meta-row">
            <td>
                <span class="gso-ics-print-meta-label">Fund Cluster :</span>
                <span class="gso-ics-print-meta-value">{{ $document['fund_cluster'] ?? '' }}</span>
            </td>
            <td>
                <span class="gso-ics-print-meta-label">Issued Date :</span>
                <span class="gso-ics-print-meta-value">{{ $document['issued_date_label'] ?? '' }}</span>
            </td>
        </tr>
    </tbody>
</table>
