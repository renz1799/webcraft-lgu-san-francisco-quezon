@php
    $document = $report['document'] ?? [];
    $entityName = ($document['entity_name'] ?? '') ?: 'Local Government Unit';
    $fundSource = ($document['fund_source'] ?? '') ?: '-';
    $supplier = ($document['supplier'] ?? '') ?: '-';
    $airNo = ($document['air_no'] ?? '') ?: '-';
    $poDetails = ($document['po_number'] ?? '') ?: '-';
    if (!empty($document['po_date_label'] ?? null)) {
        $poDetails .= ' / ' . $document['po_date_label'];
    }
    $airDate = ($document['air_date_label'] ?? '') ?: '-';
    $officeDepartment = ($document['office_department'] ?? '') ?: '-';
    $invoiceNo = ($document['invoice_no'] ?? '') ?: '-';
    $invoiceDate = ($document['invoice_date_label'] ?? '') ?: '-';
@endphp

<tr class="gso-air-print-info-row">
    <td class="gso-air-print-meta-label">LGU :</td>
    <td class="gso-air-print-meta-value">{{ $entityName }}</td>
    <td colspan="2" class="gso-air-print-meta-inline"><strong>Fund :</strong> {{ $fundSource }}</td>
</tr>
<tr class="gso-air-print-info-row">
    <td class="gso-air-print-meta-label">Supplier :</td>
    <td class="gso-air-print-meta-value">{{ $supplier }}</td>
    <td colspan="2" class="gso-air-print-meta-inline"><strong>AIR No. :</strong> {{ $airNo }}</td>
</tr>
<tr class="gso-air-print-info-row">
    <td class="gso-air-print-meta-label">PO No./Date :</td>
    <td class="gso-air-print-meta-value">{{ $poDetails }}</td>
    <td colspan="2" class="gso-air-print-meta-inline"><strong>Date :</strong> {{ $airDate }}</td>
</tr>
<tr class="gso-air-print-info-row">
    <td class="gso-air-print-meta-label" rowspan="2">Req. Office/Dept :</td>
    <td class="gso-air-print-meta-value" rowspan="2">{{ $officeDepartment }}</td>
    <td colspan="2" class="gso-air-print-meta-inline"><strong>Invoice No. :</strong> {{ $invoiceNo }}</td>
</tr>
<tr class="gso-air-print-info-row">
    <td colspan="2" class="gso-air-print-meta-inline"><strong>Date :</strong> {{ $invoiceDate }}</td>
</tr>
