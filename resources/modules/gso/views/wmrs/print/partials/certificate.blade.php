@php
    $document = $report['document'] ?? [];
@endphp

<table class="gso-wmr-print-certificate-table gso-wmr-print-stack-next">
    <colgroup>
        <col style="width: 100%;">
    </colgroup>
    <tbody>
        <tr class="gso-wmr-print-sign-title-row">
            <td>CERTIFICATE OF INSPECTION</td>
        </tr>
        <tr>
            <td class="gso-wmr-print-certificate-body">
                <p class="gso-wmr-print-certificate-copy">
                    I hereby certify that the property enumerated above was disposed of as follows:
                </p>

                <table class="gso-wmr-print-certificate-methods">
                    <tbody>
                        <tr>
                            <td style="width: 12%;">Item</td>
                            <td class="gso-wmr-print-certificate-fill" style="width: 18%;">{{ $document['destroyed_lines'] ?: ' ' }}</td>
                            <td style="width: 70%; padding-left: 4mm;">Destroyed</td>
                        </tr>
                        <tr>
                            <td>Item</td>
                            <td class="gso-wmr-print-certificate-fill">{{ $document['private_sale_lines'] ?: ' ' }}</td>
                            <td style="padding-left: 4mm;">Sold at private sale</td>
                        </tr>
                        <tr>
                            <td>Item</td>
                            <td class="gso-wmr-print-certificate-fill">{{ $document['public_auction_lines'] ?: ' ' }}</td>
                            <td style="padding-left: 4mm;">Sold at public auction</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">Item</td>
                            <td colspan="2" style="padding-left: 0;">
                                <span class="gso-wmr-print-certificate-transfer">{{ $document['transfer_summary'] ?: ' ' }}</span>
                                <span style="padding-left: 4mm;">Transferred without cost to another agency/entity</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>

<table class="gso-wmr-print-sign-table gso-wmr-print-stack-next">
    <colgroup>
        <col style="width: 50%;">
        <col style="width: 50%;">
    </colgroup>
    <tbody>
        <tr class="gso-wmr-print-sign-title-row">
            <td>Inspection Officer :</td>
            <td>Witness to Disposal :</td>
        </tr>
        <tr>
            <td class="gso-wmr-print-sign-field-cell">
                <div class="gso-wmr-print-sign-field">{{ $document['inspection_officer_name'] ?: ' ' }}</div>
            </td>
            <td class="gso-wmr-print-sign-field-cell">
                <div class="gso-wmr-print-sign-field">{{ $document['witness_name'] ?: ' ' }}</div>
            </td>
        </tr>
        <tr>
            <td class="gso-wmr-print-sign-caption">Signature over Printed Name of Inspection Officer</td>
            <td class="gso-wmr-print-sign-caption">Signature over Printed Name of Witness</td>
        </tr>
        <tr>
            <td class="gso-wmr-print-sign-designation">{{ $document['inspection_officer_designation'] ?: ' ' }}</td>
            <td class="gso-wmr-print-sign-designation">{{ $document['witness_designation'] ?: ' ' }}</td>
        </tr>
    </tbody>
</table>
