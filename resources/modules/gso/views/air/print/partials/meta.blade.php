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
    <td class="gso-air-print-meta-label gso-air-print-col--property" style="width:16%;">LGU :</td>
    <td class="gso-air-print-meta-value gso-air-print-col--description" style="width:54%;">{{ $entityName }}</td>
    <td class="gso-air-print-meta-label gso-air-print-col--unit" style="width:12%;">Fund :</td>
    <td class="gso-air-print-meta-value gso-air-print-col--quantity" style="width:18%;">{{ $fundSource }}</td>
</tr>
<tr class="gso-air-print-info-row">
    <td class="gso-air-print-meta-label gso-air-print-col--property" style="width:16%;">Supplier :</td>
    <td class="gso-air-print-meta-value gso-air-print-col--description" style="width:54%;">{{ $supplier }}</td>
    <td class="gso-air-print-meta-label gso-air-print-col--unit" style="width:12%;">AIR No. :</td>
    <td class="gso-air-print-meta-value gso-air-print-col--quantity" style="width:18%;">{{ $airNo }}</td>
</tr>
<tr class="gso-air-print-info-row">
    <td class="gso-air-print-meta-label gso-air-print-col--property" style="width:16%;">PO No./Date :</td>
    <td class="gso-air-print-meta-value gso-air-print-col--description" style="width:54%;">{{ $poDetails }}</td>
    <td class="gso-air-print-meta-label gso-air-print-col--unit" style="width:12%;">Date :</td>
    <td class="gso-air-print-meta-value gso-air-print-col--quantity" style="width:18%;">{{ $airDate }}</td>
</tr>
<tr class="gso-air-print-info-row">
    <td class="gso-air-print-meta-label gso-air-print-col--property" rowspan="2" style="width:16%;">Req. Office/Dept :</td>
    <td class="gso-air-print-meta-value gso-air-print-col--description" rowspan="2" style="width:54%;">{{ $officeDepartment }}</td>
    <td class="gso-air-print-meta-label gso-air-print-col--unit" style="width:12%;">Invoice No. :</td>
    <td class="gso-air-print-meta-value gso-air-print-col--quantity" style="width:18%;">{{ $invoiceNo }}</td>
</tr>
<tr class="gso-air-print-info-row">
    <td class="gso-air-print-meta-label gso-air-print-col--unit" style="width:12%;">Date :</td>
    <td class="gso-air-print-meta-value gso-air-print-col--quantity" style="width:18%;">{{ $invoiceDate }}</td>
</tr>
