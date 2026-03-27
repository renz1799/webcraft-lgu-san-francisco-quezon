@php
    $document = $report['document'] ?? [];
@endphp

<table class="gso-wmr-print-sheet">
    <colgroup>
        <col style="width: 18%;">
        <col style="width: 42%;">
        <col style="width: 15%;">
        <col style="width: 25%;">
    </colgroup>
    <tbody>
        <tr class="gso-wmr-print-meta-row">
            <td class="gso-wmr-print-meta-label">Entity Name :</td>
            <td class="gso-wmr-print-meta-value">{{ $document['entity_name'] ?? '' }}</td>
            <td class="gso-wmr-print-meta-label">Fund Cluster :</td>
            <td class="gso-wmr-print-meta-value">{{ $document['fund_cluster'] ?? '' }}</td>
        </tr>
        <tr class="gso-wmr-print-meta-row">
            <td class="gso-wmr-print-meta-label">Place of Storage :</td>
            <td class="gso-wmr-print-meta-value">{{ $document['place_of_storage'] ?? '' }}</td>
            <td class="gso-wmr-print-meta-label">Date :</td>
            <td class="gso-wmr-print-meta-value">{{ $document['report_date_label'] ?? '' }}</td>
        </tr>
    </tbody>
</table>
