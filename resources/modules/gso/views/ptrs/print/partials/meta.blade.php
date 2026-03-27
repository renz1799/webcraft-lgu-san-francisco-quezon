@php
    $document = $report['document'] ?? [];
    $transferType = strtolower((string) ($document['transfer_type'] ?? ''));
    $transferTypeOther = (string) ($document['transfer_type_other'] ?? '');
@endphp

<table class="gso-ptr-print-sheet">
    <colgroup>
        <col style="width: 15%;">
        <col style="width: 57%;">
        <col style="width: 13%;">
        <col style="width: 15%;">
    </colgroup>
    <tbody>
        <tr class="gso-ptr-print-meta-row">
            <td class="gso-ptr-print-meta-label">Entity :</td>
            <td class="gso-ptr-print-meta-value">{{ $document['entity_name'] ?? '' }}</td>
            <td class="gso-ptr-print-meta-label">Fund Cl. :</td>
            <td class="gso-ptr-print-meta-value">{{ $document['fund_cluster'] ?? '' }}</td>
        </tr>
        <tr class="gso-ptr-print-meta-row">
            <td class="gso-ptr-print-meta-label">From A.O./Off./FC :</td>
            <td class="gso-ptr-print-meta-value">{{ $document['from_summary'] ?? '' }}</td>
            <td class="gso-ptr-print-meta-label">PTR No. :</td>
            <td class="gso-ptr-print-meta-value">{{ $document['ptr_number'] ?? '' }}</td>
        </tr>
        <tr class="gso-ptr-print-meta-row">
            <td class="gso-ptr-print-meta-label">To A.O./Off./FC :</td>
            <td class="gso-ptr-print-meta-value">{{ $document['to_summary'] ?? '' }}</td>
            <td class="gso-ptr-print-meta-label">Date :</td>
            <td class="gso-ptr-print-meta-value">{{ $document['transfer_date_label'] ?? '' }}</td>
        </tr>
        <tr class="gso-ptr-print-meta-row">
            <td class="gso-ptr-print-meta-label" style="vertical-align: top;">
                Transfer Type:<br>
                <span style="font-size: 10px;">(check 1)</span>
            </td>
            <td colspan="3">
                <div class="gso-ptr-print-transfer-options">
                    <span class="gso-ptr-print-type-option">[{{ $transferType === 'donation' ? 'x' : ' ' }}] Donation</span>
                    <span class="gso-ptr-print-type-option">[{{ $transferType === 'relocate' ? 'x' : ' ' }}] Relocate</span>
                </div>
                <div class="gso-ptr-print-transfer-options">
                    <span class="gso-ptr-print-type-option">[{{ $transferType === 'reassignment' ? 'x' : ' ' }}] Reassignment</span>
                    <span class="gso-ptr-print-type-option">
                        [{{ $transferType === 'others' ? 'x' : ' ' }}] Others (Specify){{ $transferType === 'others' && $transferTypeOther !== '' ? ': ' . $transferTypeOther : '' }}
                    </span>
                </div>
            </td>
        </tr>
    </tbody>
</table>
