<table class="form-table">
  <colgroup>
    <col style="width: 5%;">
    <col style="width: 8%;">
    <col style="width: 9%;">
    <col style="width: 13%;">
    <col style="width: 30%;">
    <col style="width: 14%;">
    <col style="width: 12%;">
  </colgroup>

  <tr>
    <td colspan="5" style="padding:0;">
      <table class="header-inner">
        <colgroup>
          <col style="width: 18%;">
          <col style="width: 82%;">
        </colgroup>
        <tr>
          <td class="divider bold">Entity Name:</td>
          <td class="bold upper">{{ $print['entity_name'] ?? '' }}</td>
        </tr>
      </table>
    </td>

    <td colspan="2" style="padding:0;">
      <table class="header-inner">
        <colgroup>
          <col style="width: 40%;">
          <col style="width: 60%;">
        </colgroup>
        <tr>
          <td class="bold">ICS No.:</td>
          <td>{{ $print['ics_no'] ?? ($ics->ics_number ?? '') }}</td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td colspan="5" style="padding:0;">
      <table class="header-inner">
        <colgroup>
          <col style="width: 18%;">
          <col style="width: 82%;">
        </colgroup>
        <tr>
          <td class="divider bold">Fund Cluster:</td>
          <td>{{ $print['fund_cluster_code'] ?? '' }}</td>
        </tr>
      </table>
    </td>

    <td colspan="2" style="padding:0;">
      <table class="header-inner">
        <colgroup>
          <col style="width: 40%;">
          <col style="width: 60%;">
        </colgroup>
        <tr>
          <td class="bold">Issued Date:</td>
          <td>{{ !empty($ics->issued_date) ? \Illuminate\Support\Carbon::parse($ics->issued_date)->format('m/d/Y') : '' }}</td>
        </tr>
      </table>
    </td>
  </tr>
</table>