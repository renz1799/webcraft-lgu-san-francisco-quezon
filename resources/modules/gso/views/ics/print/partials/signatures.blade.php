@php
    $document = $report['document'] ?? [];
    $receivedFromRole = trim(
        (string) ($document['received_from_position'] ?? '')
        . (!empty($document['received_from_office'] ?? null) ? ' / ' . (string) $document['received_from_office'] : '')
    );
    $receivedByRole = trim(
        (string) ($document['received_by_position'] ?? '')
        . (!empty($document['received_by_office'] ?? null) ? ' / ' . (string) $document['received_by_office'] : '')
    );
@endphp

<table class="gso-ics-print-signatures gso-ics-print-stack-next">
    <colgroup>
        <col style="width:50%;">
        <col style="width:50%;">
    </colgroup>
    <tbody>
        <tr>
            <td class="gso-ics-print-signature-cell">
                <div class="gso-ics-print-signature-title">Received from:</div>

                <div class="gso-ics-print-signature-line">{{ ($document['received_from_name'] ?? '') !== '' ? $document['received_from_name'] : ' ' }}</div>
                <div class="gso-ics-print-signature-caption">Signature Over Printed Name</div>

                <div class="gso-ics-print-signature-line">{{ $receivedFromRole !== '' ? $receivedFromRole : ' ' }}</div>
                <div class="gso-ics-print-signature-caption">Position/Office</div>

                <div class="gso-ics-print-signature-line">{{ ($document['received_from_date_label'] ?? '') !== '' ? $document['received_from_date_label'] : ' ' }}</div>
                <div class="gso-ics-print-signature-caption">Date</div>
            </td>

            <td class="gso-ics-print-signature-cell">
                <div class="gso-ics-print-signature-title">Received by:</div>

                <div class="gso-ics-print-signature-line">{{ ($document['received_by_name'] ?? '') !== '' ? $document['received_by_name'] : ' ' }}</div>
                <div class="gso-ics-print-signature-caption">Signature Over Printed Name</div>

                <div class="gso-ics-print-signature-line">{{ $receivedByRole !== '' ? $receivedByRole : ' ' }}</div>
                <div class="gso-ics-print-signature-caption">Position/Office</div>

                <div class="gso-ics-print-signature-line">{{ ($document['received_by_date_label'] ?? '') !== '' ? $document['received_by_date_label'] : ' ' }}</div>
                <div class="gso-ics-print-signature-caption">Date</div>
            </td>
        </tr>
    </tbody>
</table>
