@php
    $document = $report['document'] ?? [];
@endphp

<table class="gso-ris-print-signatures gso-ris-print-stack-next" style="margin-top: 3mm;">
    <colgroup>
        <col style="width: 25%;">
        <col style="width: 25%;">
        <col style="width: 25%;">
        <col style="width: 25%;">
    </colgroup>

    <tr>
        <td>
            <div class="gso-ris-print-signature-cell">
                <div class="gso-ris-print-signature-title">Requested by:</div>
                <div class="gso-ris-print-signature-line">{{ $document['requested_by_name'] ?? '' }}</div>
                <div class="gso-ris-print-signature-caption">Signature Over Printed Name</div>
                <div class="gso-ris-print-signature-line">{{ $document['requested_by_designation'] ?? '' }}</div>
                <div class="gso-ris-print-signature-caption">Designation</div>
                <div class="gso-ris-print-signature-line">{{ $document['requested_by_date_label'] ?? '' }}</div>
                <div class="gso-ris-print-signature-caption">Date</div>
            </div>
        </td>
        <td>
            <div class="gso-ris-print-signature-cell">
                <div class="gso-ris-print-signature-title">Approved by:</div>
                <div class="gso-ris-print-signature-line">{{ $document['approved_by_name'] ?? '' }}</div>
                <div class="gso-ris-print-signature-caption">Signature Over Printed Name</div>
                <div class="gso-ris-print-signature-line">{{ $document['approved_by_designation'] ?? '' }}</div>
                <div class="gso-ris-print-signature-caption">Designation</div>
                <div class="gso-ris-print-signature-line">{{ $document['approved_by_date_label'] ?? '' }}</div>
                <div class="gso-ris-print-signature-caption">Date</div>
            </div>
        </td>
        <td>
            <div class="gso-ris-print-signature-cell">
                <div class="gso-ris-print-signature-title">Issued by:</div>
                <div class="gso-ris-print-signature-line">{{ $document['issued_by_name'] ?? '' }}</div>
                <div class="gso-ris-print-signature-caption">Signature Over Printed Name</div>
                <div class="gso-ris-print-signature-line">{{ $document['issued_by_designation'] ?? '' }}</div>
                <div class="gso-ris-print-signature-caption">Designation</div>
                <div class="gso-ris-print-signature-line">{{ $document['issued_by_date_label'] ?? '' }}</div>
                <div class="gso-ris-print-signature-caption">Date</div>
            </div>
        </td>
        <td>
            <div class="gso-ris-print-signature-cell">
                <div class="gso-ris-print-signature-title">Received by:</div>
                <div class="gso-ris-print-signature-line">{{ $document['received_by_name'] ?? '' }}</div>
                <div class="gso-ris-print-signature-caption">Signature Over Printed Name</div>
                <div class="gso-ris-print-signature-line">{{ $document['received_by_designation'] ?? '' }}</div>
                <div class="gso-ris-print-signature-caption">Designation</div>
                <div class="gso-ris-print-signature-line">{{ $document['received_by_date_label'] ?? '' }}</div>
                <div class="gso-ris-print-signature-caption">Date</div>
            </div>
        </td>
    </tr>
</table>
