@php
    $document = $report['document'] ?? [];
@endphp

<table class="gso-ptr-print-signatures gso-ptr-print-stack-next">
    <colgroup>
        <col style="width: 14%;">
        <col style="width: 28.666%;">
        <col style="width: 28.666%;">
        <col style="width: 28.666%;">
    </colgroup>
    <tbody>
        <tr>
            <td class="gso-ptr-print-signature-data-cell">&nbsp;</td>
            <td class="gso-ptr-print-signature-data-cell gso-ptr-print-center"><strong>Approved by:</strong></td>
            <td class="gso-ptr-print-signature-data-cell gso-ptr-print-center"><strong>Released/Issued by:</strong></td>
            <td class="gso-ptr-print-signature-data-cell gso-ptr-print-center"><strong>Received by:</strong></td>
        </tr>
        <tr>
            <td class="gso-ptr-print-signature-label-cell">Signature :</td>
            <td class="gso-ptr-print-signature-data-cell"><div class="gso-ptr-print-signature-field">&nbsp;</div></td>
            <td class="gso-ptr-print-signature-data-cell"><div class="gso-ptr-print-signature-field">&nbsp;</div></td>
            <td class="gso-ptr-print-signature-data-cell"><div class="gso-ptr-print-signature-field">&nbsp;</div></td>
        </tr>
        <tr>
            <td class="gso-ptr-print-signature-label-cell">Printed Name :</td>
            <td class="gso-ptr-print-signature-data-cell"><div class="gso-ptr-print-signature-field">{{ $document['approved_by_name'] !== '' ? $document['approved_by_name'] : ' ' }}</div></td>
            <td class="gso-ptr-print-signature-data-cell"><div class="gso-ptr-print-signature-field">{{ $document['released_by_name'] !== '' ? $document['released_by_name'] : ' ' }}</div></td>
            <td class="gso-ptr-print-signature-data-cell"><div class="gso-ptr-print-signature-field">{{ $document['received_by_name'] !== '' ? $document['received_by_name'] : ' ' }}</div></td>
        </tr>
        <tr>
            <td class="gso-ptr-print-signature-label-cell">Designation :</td>
            <td class="gso-ptr-print-signature-data-cell"><div class="gso-ptr-print-signature-field">{{ $document['approved_by_designation'] !== '' ? $document['approved_by_designation'] : ' ' }}</div></td>
            <td class="gso-ptr-print-signature-data-cell"><div class="gso-ptr-print-signature-field">{{ $document['released_by_designation'] !== '' ? $document['released_by_designation'] : ' ' }}</div></td>
            <td class="gso-ptr-print-signature-data-cell"><div class="gso-ptr-print-signature-field">{{ $document['received_by_designation'] !== '' ? $document['received_by_designation'] : ' ' }}</div></td>
        </tr>
        <tr>
            <td class="gso-ptr-print-signature-label-cell">Date :</td>
            <td class="gso-ptr-print-signature-data-cell"><div class="gso-ptr-print-signature-field">{{ $document['approved_by_date_label'] !== '' ? $document['approved_by_date_label'] : ' ' }}</div></td>
            <td class="gso-ptr-print-signature-data-cell"><div class="gso-ptr-print-signature-field">{{ $document['released_by_date_label'] !== '' ? $document['released_by_date_label'] : ' ' }}</div></td>
            <td class="gso-ptr-print-signature-data-cell"><div class="gso-ptr-print-signature-field">{{ $document['received_by_date_label'] !== '' ? $document['received_by_date_label'] : ' ' }}</div></td>
        </tr>
    </tbody>
</table>
