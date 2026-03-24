@php
    $formatDate = fn ($date) => !empty($date) ? \Illuminate\Support\Carbon::parse($date)->format('m/d/Y') : '';
    $lineStyle = 'border-bottom:1px solid #000; min-height:18px; line-height:18px; text-align:center; margin:10px auto 2px; width:90%; padding:0 6px; font-weight:700;';
    $captionStyle = 'text-align:center; font-size:10px; margin-bottom:8px;';
@endphp

<table class="form-table stack-next" style="margin-top: 3mm;">
    <colgroup>
        <col style="width: 25%;">
        <col style="width: 25%;">
        <col style="width: 25%;">
        <col style="width: 25%;">
    </colgroup>

    <tr>
        <td style="padding: 0; vertical-align: top;">
            <div style="padding: 8px 8px 10px; min-height: 40mm;">
                <div style="margin-bottom: 12px; text-align: center; font-weight: 700;">Requested by:</div>
                <div style="{{ $lineStyle }}">{{ $ris->requested_by_name ?? '' }}</div>
                <div style="{{ $captionStyle }}">Signature Over Printed Name</div>
                <div style="{{ $lineStyle }}">{{ $ris->requested_by_designation ?? '' }}</div>
                <div style="{{ $captionStyle }}">Designation</div>
                <div style="{{ $lineStyle }}">{{ $formatDate($ris->requested_by_date) }}</div>
                <div style="{{ $captionStyle }}">Date</div>
            </div>
        </td>
        <td style="padding: 0; vertical-align: top;">
            <div style="padding: 8px 8px 10px; min-height: 40mm;">
                <div style="margin-bottom: 12px; text-align: center; font-weight: 700;">Approved by:</div>
                <div style="{{ $lineStyle }}">{{ $ris->approved_by_name ?? '' }}</div>
                <div style="{{ $captionStyle }}">Signature Over Printed Name</div>
                <div style="{{ $lineStyle }}">{{ $ris->approved_by_designation ?? '' }}</div>
                <div style="{{ $captionStyle }}">Designation</div>
                <div style="{{ $lineStyle }}">{{ $formatDate($ris->approved_by_date) }}</div>
                <div style="{{ $captionStyle }}">Date</div>
            </div>
        </td>
        <td style="padding: 0; vertical-align: top;">
            <div style="padding: 8px 8px 10px; min-height: 40mm;">
                <div style="margin-bottom: 12px; text-align: center; font-weight: 700;">Issued by:</div>
                <div style="{{ $lineStyle }}">{{ $ris->issued_by_name ?? '' }}</div>
                <div style="{{ $captionStyle }}">Signature Over Printed Name</div>
                <div style="{{ $lineStyle }}">{{ $ris->issued_by_designation ?? '' }}</div>
                <div style="{{ $captionStyle }}">Designation</div>
                <div style="{{ $lineStyle }}">{{ $formatDate($ris->issued_by_date) }}</div>
                <div style="{{ $captionStyle }}">Date</div>
            </div>
        </td>
        <td style="padding: 0; vertical-align: top;">
            <div style="padding: 8px 8px 10px; min-height: 40mm;">
                <div style="margin-bottom: 12px; text-align: center; font-weight: 700;">Received by:</div>
                <div style="{{ $lineStyle }}">{{ $ris->received_by_name ?? '' }}</div>
                <div style="{{ $captionStyle }}">Signature Over Printed Name</div>
                <div style="{{ $lineStyle }}">{{ $ris->received_by_designation ?? '' }}</div>
                <div style="{{ $captionStyle }}">Designation</div>
                <div style="{{ $lineStyle }}">{{ $formatDate($ris->received_by_date) }}</div>
                <div style="{{ $captionStyle }}">Date</div>
            </div>
        </td>
    </tr>
</table>
