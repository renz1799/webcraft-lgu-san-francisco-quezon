<table class="sign-table stack-next">
  <colgroup>
    <col style="width: 50%;">
    <col style="width: 50%;">
  </colgroup>
  <tbody>
    <tr>
      <td class="center bold">Certified Correct :</td>
      <td class="center bold">Disposal Approved :</td>
    </tr>
    <tr>
      <td class="sig-cell"><div class="sig-value">&nbsp;</div></td>
      <td class="sig-cell"><div class="sig-value">&nbsp;</div></td>
    </tr>
    <tr>
      <td class="center small">Signature over Printed Name of Supply<br>and/or Property Custodian</td>
      <td class="center small">Signature over Printed Name of Head of Agency/Entity<br>or his/her Authorized Representative</td>
    </tr>
    <tr>
      <td class="center bold" style="padding-top: 2mm;">{{ $wmr->custodian_name ?: ' ' }}</td>
      <td class="center bold" style="padding-top: 2mm;">{{ $wmr->approved_by_name ?: ' ' }}</td>
    </tr>
    <tr>
      <td class="center small">{{ $wmr->custodian_designation ?: ' ' }}</td>
      <td class="center small">{{ $wmr->approved_by_designation ?: ' ' }}</td>
    </tr>
  </tbody>
</table>