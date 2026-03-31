@php
    $document = $report['document'] ?? [];
    $entityName = ($document['entity_name'] ?? '') ?: 'Local Government Unit';
    $icsNo = ($document['ics_no'] ?? '') ?: '-';
    $fundCluster = ($document['fund_cluster'] ?? '') ?: '-';
    $issuedDate = ($document['issued_date_label'] ?? '') ?: '-';

    $leftBlockWidth = '70%';
    $rightBlockWidth = '30%';
@endphp

<table class="gso-ics-print-sheet gso-ics-print-meta-table">
    <tbody>
        <tr class="gso-ics-print-meta-row">
            <td class="gso-ics-print-col--meta-value" style="width: {{ $leftBlockWidth }};">
                <span class="gso-ics-print-meta-label">Entity Name :</span>
                <span class="gso-ics-print-meta-value">{{ $entityName }}</span>
            </td>
            <td class="gso-ics-print-col--inventory-item-no" style="width: {{ $rightBlockWidth }};">
                <span class="gso-ics-print-meta-label">ICS No. :</span>
                <span class="gso-ics-print-meta-value gso-ics-print-meta-value--right">{{ $icsNo }}</span>
            </td>
        </tr>
        <tr class="gso-ics-print-meta-row">
            <td class="gso-ics-print-col--meta-value" style="width: {{ $leftBlockWidth }};">
                <span class="gso-ics-print-meta-label">Fund Cluster :</span>
                <span class="gso-ics-print-meta-value">{{ $fundCluster }}</span>
            </td>
            <td class="gso-ics-print-col--inventory-item-no" style="width: {{ $rightBlockWidth }};">
                <span class="gso-ics-print-meta-label">Issued Date :</span>
                <span class="gso-ics-print-meta-value gso-ics-print-meta-value--right">{{ $issuedDate }}</span>
            </td>
        </tr>
    </tbody>
</table>
