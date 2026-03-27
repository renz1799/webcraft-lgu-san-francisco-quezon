@php
    $document = $report['document'] ?? [];
    $issuedByRole = trim(
        (string) ($document['issued_by_position'] ?? '')
        . (!empty($document['issued_by_office'] ?? null) ? ' / ' . (string) $document['issued_by_office'] : '')
    );
@endphp

<table class="gso-par-print-signatures gso-par-print-stack-next">
    <colgroup>
        <col style="width:50%;">
        <col style="width:50%;">
    </colgroup>
    <tbody>
        <tr>
            <td class="gso-par-print-signature-cell">
                <div class="gso-par-print-signature-title">Received by:</div>

                <div class="gso-par-print-signature-line"> </div>
                <div class="gso-par-print-signature-caption">Signature over Printed Name of End User</div>

                <div class="gso-par-print-signature-line">{{ ($document['received_by_name'] ?? '') !== '' ? $document['received_by_name'] : ' ' }}</div>
                <div class="gso-par-print-signature-caption">Printed Name</div>

                <div class="gso-par-print-signature-line">{{ ($document['received_by_position'] ?? '') !== '' ? $document['received_by_position'] : ' ' }}</div>
                <div class="gso-par-print-signature-caption">Position/Office</div>

                <div class="gso-par-print-signature-line">{{ ($document['received_by_date_label'] ?? '') !== '' ? $document['received_by_date_label'] : ' ' }}</div>
                <div class="gso-par-print-signature-caption">Date</div>
            </td>

            <td class="gso-par-print-signature-cell">
                <div class="gso-par-print-signature-title">Issued by:</div>

                <div class="gso-par-print-signature-line"> </div>
                <div class="gso-par-print-signature-caption">Signature over Printed Name of Supply and/or Property Custodian</div>

                <div class="gso-par-print-signature-line">{{ ($document['issued_by_name'] ?? '') !== '' ? $document['issued_by_name'] : ' ' }}</div>
                <div class="gso-par-print-signature-caption">Printed Name</div>

                <div class="gso-par-print-signature-line">{{ $issuedByRole !== '' ? $issuedByRole : ' ' }}</div>
                <div class="gso-par-print-signature-caption">Position/Office</div>

                <div class="gso-par-print-signature-line">{{ ($document['issued_by_date_label'] ?? '') !== '' ? $document['issued_by_date_label'] : ' ' }}</div>
                <div class="gso-par-print-signature-caption">Date</div>
            </td>
        </tr>
    </tbody>
</table>
