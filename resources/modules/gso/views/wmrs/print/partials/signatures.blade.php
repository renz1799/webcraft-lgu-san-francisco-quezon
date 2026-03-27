@php
    $document = $report['document'] ?? [];
@endphp

<table class="gso-wmr-print-sign-table gso-wmr-print-stack-next">
    <colgroup>
        <col style="width: 50%;">
        <col style="width: 50%;">
    </colgroup>
    <tbody>
        <tr class="gso-wmr-print-sign-title-row">
            <td>Certified Correct :</td>
            <td>Disposal Approved :</td>
        </tr>
        <tr>
            <td class="gso-wmr-print-sign-field-cell">
                <div class="gso-wmr-print-sign-field">{{ $document['custodian_name'] ?: ' ' }}</div>
            </td>
            <td class="gso-wmr-print-sign-field-cell">
                <div class="gso-wmr-print-sign-field">{{ $document['approved_by_name'] ?: ' ' }}</div>
            </td>
        </tr>
        <tr>
            <td class="gso-wmr-print-sign-caption">Signature over Printed Name of Supply and/or Property Custodian</td>
            <td class="gso-wmr-print-sign-caption">Signature over Printed Name of Head of Agency/Entity or Authorized Representative</td>
        </tr>
        <tr>
            <td class="gso-wmr-print-sign-designation">{{ $document['custodian_designation'] ?: ' ' }}</td>
            <td class="gso-wmr-print-sign-designation">{{ $document['approved_by_designation'] ?: ' ' }}</td>
        </tr>
    </tbody>
</table>
