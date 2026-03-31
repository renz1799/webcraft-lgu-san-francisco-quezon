@php
    $document = $report['document'] ?? [];
    $entityName = ($document['entity_name'] ?? '') ?: 'Local Government Unit';
    $issuedDate = ($document['issued_date_label'] ?? '') ?: '-';
    $fundCluster = ($document['fund_cluster'] ?? '') ?: '-';
    $parNumber = ($document['par_number'] ?? '') ?: '-';

    $leftLabelWidth = '16%';
    $leftValueWidth = '54%';
    $rightLabelWidth = '15%';
    $rightValueWidth = '15%';
@endphp

<table class="gso-par-print-sheet">
    <tbody>
        <tr class="gso-par-print-meta-row">
            <td class="gso-par-print-meta-label gso-par-print-col--meta-label" style="width: {{ $leftLabelWidth }};">Entity Name :</td>
            <td class="gso-par-print-meta-value gso-par-print-col--meta-value" style="width: {{ $leftValueWidth }};">{{ $entityName }}</td>
            <td class="gso-par-print-meta-label gso-par-print-col--date-acquired gso-par-print-col-head--compact" style="width: {{ $rightLabelWidth }};">Date :</td>
            <td class="gso-par-print-meta-value gso-par-print-col--amount gso-par-print-meta-value--right" style="width: {{ $rightValueWidth }};">{{ $issuedDate }}</td>
        </tr>
        <tr class="gso-par-print-meta-row">
            <td class="gso-par-print-meta-label gso-par-print-col--meta-label" style="width: {{ $leftLabelWidth }};">Fund Cluster :</td>
            <td class="gso-par-print-meta-value gso-par-print-col--meta-value" style="width: {{ $leftValueWidth }};">{{ $fundCluster }}</td>
            <td class="gso-par-print-meta-label gso-par-print-col--date-acquired gso-par-print-col-head--compact" style="width: {{ $rightLabelWidth }};">PAR No. :</td>
            <td class="gso-par-print-meta-value gso-par-print-col--amount gso-par-print-meta-value--right" style="width: {{ $rightValueWidth }};">{{ $parNumber }}</td>
        </tr>
    </tbody>
</table>
