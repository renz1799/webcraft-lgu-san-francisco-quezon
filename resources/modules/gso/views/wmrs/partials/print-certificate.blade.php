@php
  $destroyedLines = $print['destroyed_lines'] ?? '';
  $privateSaleLines = $print['private_sale_lines'] ?? '';
  $publicAuctionLines = $print['public_auction_lines'] ?? '';
  $transferSummary = $print['transfer_summary'] ?? '';
@endphp

<table class="certificate-table stack-next">
  <colgroup>
    <col style="width: 100%;">
  </colgroup>
  <tbody>
    <tr>
      <td class="center bold">CERTIFICATE OF INSPECTION</td>
    </tr>
    <tr>
      <td>
        <div class="small" style="margin-bottom: 3mm;">I hereby certify that the property enumerated above was disposed of as follows:</div>
        <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
          <tbody>
            <tr>
              <td style="border: none; width: 12%;">Item</td>
              <td style="border: none; width: 18%; border-bottom: 1px solid #000;">{{ $destroyedLines !== '' ? $destroyedLines : ' ' }}</td>
              <td style="border: none; width: 70%; padding-left: 4mm;">Destroyed</td>
            </tr>
            <tr>
              <td style="border: none; width: 12%;">Item</td>
              <td style="border: none; width: 18%; border-bottom: 1px solid #000;">{{ $privateSaleLines !== '' ? $privateSaleLines : ' ' }}</td>
              <td style="border: none; width: 70%; padding-left: 4mm;">Sold at private sale</td>
            </tr>
            <tr>
              <td style="border: none; width: 12%;">Item</td>
              <td style="border: none; width: 18%; border-bottom: 1px solid #000;">{{ $publicAuctionLines !== '' ? $publicAuctionLines : ' ' }}</td>
              <td style="border: none; width: 70%; padding-left: 4mm;">Sold at public auction</td>
            </tr>
            <tr>
              <td style="border: none; width: 12%;">Item</td>
              <td colspan="2" style="border: none; padding-left: 0;">
                <span style="display: inline-block; min-width: 58%; border-bottom: 1px solid #000; text-align: left; padding-left: 2mm;">{{ $transferSummary !== '' ? $transferSummary : ' ' }}</span>
                <span style="padding-left: 4mm;">Transferred without cost to</span>
                <span style="display: inline-block; min-width: 22%; border-bottom: 1px solid #000;">&nbsp;</span>
                <span>(Name of the Agency/Entity)</span>
              </td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
  </tbody>
</table>

<table class="sign-table stack-next">
  <colgroup>
    <col style="width: 50%;">
    <col style="width: 50%;">
  </colgroup>
  <tbody>
    <tr>
      <td class="center bold">Certified Correct :</td>
      <td class="center bold">Witness to Disposal:</td>
    </tr>
    <tr>
      <td class="sig-cell"><div class="sig-value">&nbsp;</div></td>
      <td class="sig-cell"><div class="sig-value">&nbsp;</div></td>
    </tr>
    <tr>
      <td class="center small">Signature over Printed Name of Inspection<br>Officer</td>
      <td class="center small">Signature over Printed Name of<br>Witness</td>
    </tr>
    <tr>
      <td class="center bold" style="padding-top: 2mm;">{{ $wmr->inspection_officer_name ?: ' ' }}</td>
      <td class="center bold" style="padding-top: 2mm;">{{ $wmr->witness_name ?: ' ' }}</td>
    </tr>
    <tr>
      <td class="center small">{{ $wmr->inspection_officer_designation ?: ' ' }}</td>
      <td class="center small">{{ $wmr->witness_designation ?: ' ' }}</td>
    </tr>
  </tbody>
</table>