@php
    $document = $report['document'] ?? [];
    $entityName = ($document['entity_name'] ?? '') ?: 'Local Government Unit';
    $fund = ($document['fund'] ?? '') ?: '-';
    $division = ($document['division'] ?? '') ?: '-';
    $fppCode = ($document['fpp_code'] ?? '') ?: '-';
    $office = ($document['office'] ?? '') ?: '-';
    $risNo = ($document['ris_no'] ?? '') ?: '-';
    $risDate = ($document['ris_date_label'] ?? '') ?: '-';

    $leftLabelWidth = '15%';
    $leftValueWidth = '57%';
    $rightLabelWidth = '8%';
    $rightValueWidth = '20%';
@endphp

<table class="gso-ris-print-sheet">
    <tr class="gso-ris-print-meta-row">
        <td class="gso-ris-print-meta-label gso-ris-print-col--stock-no" style="width: {{ $leftLabelWidth }};">LGU:</td>
        <td class="gso-ris-print-meta-value gso-ris-print-col--description gso-ris-print-bold gso-ris-print-upper" style="width: {{ $leftValueWidth }};">{{ $entityName }}</td>
        <td class="gso-ris-print-meta-label gso-ris-print-col--qty gso-ris-print-col-head--compact" style="width: {{ $rightLabelWidth }};">Fund:</td>
        <td class="gso-ris-print-meta-value gso-ris-print-col--remarks gso-ris-print-meta-value--right" style="width: {{ $rightValueWidth }};">{{ $fund }}</td>
    </tr>

    <tr class="gso-ris-print-meta-row">
        <td class="gso-ris-print-meta-label gso-ris-print-col--stock-no" style="width: {{ $leftLabelWidth }};">Division:</td>
        <td class="gso-ris-print-meta-value gso-ris-print-col--description" style="width: {{ $leftValueWidth }};">{{ $division }}</td>
        <td class="gso-ris-print-meta-label gso-ris-print-col--qty gso-ris-print-col-head--compact" style="width: {{ $rightLabelWidth }};">FPP Code:</td>
        <td class="gso-ris-print-meta-value gso-ris-print-col--remarks gso-ris-print-meta-value--right" style="width: {{ $rightValueWidth }};">{{ $fppCode }}</td>
    </tr>

    <tr class="gso-ris-print-meta-row">
        <td class="gso-ris-print-meta-label gso-ris-print-col--stock-no" rowspan="2" style="width: {{ $leftLabelWidth }};">Office:</td>
        <td class="gso-ris-print-meta-value gso-ris-print-col--description" rowspan="2" style="width: {{ $leftValueWidth }};">{{ $office }}</td>
        <td class="gso-ris-print-meta-label gso-ris-print-col--qty gso-ris-print-col-head--compact" style="width: {{ $rightLabelWidth }};">RIS No.:</td>
        <td class="gso-ris-print-meta-value gso-ris-print-col--remarks gso-ris-print-meta-value--right" style="width: {{ $rightValueWidth }};">{{ $risNo }}</td>
    </tr>

    <tr class="gso-ris-print-meta-row">
        <td class="gso-ris-print-meta-label gso-ris-print-col--qty gso-ris-print-col-head--compact" style="width: {{ $rightLabelWidth }};">Date:</td>
        <td class="gso-ris-print-meta-value gso-ris-print-col--remarks gso-ris-print-meta-value--right" style="width: {{ $rightValueWidth }};">{{ $risDate }}</td>
    </tr>
</table>
