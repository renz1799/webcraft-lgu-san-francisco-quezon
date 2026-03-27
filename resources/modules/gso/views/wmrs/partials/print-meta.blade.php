<table class="form-table meta-table stack-next">
  <colgroup>
    <col style="width: 18%;">
    <col style="width: 42%;">
    <col style="width: 15%;">
    <col style="width: 25%;">
  </colgroup>
  <tbody>
    <tr>
      <td class="bold">Entity Name :</td>
      <td>{{ $print['entity_name'] ?? '' }}</td>
      <td class="bold">Fund Cluster :</td>
      <td>{{ $print['fund_cluster_code'] ?? '' }}</td>
    </tr>
    <tr>
      <td class="bold">Place of Storage :</td>
      <td>{{ $print['place_of_storage'] ?? '' }}</td>
      <td class="bold">Date :</td>
      <td>{{ $print['report_date'] ?? '' }}</td>
    </tr>
  </tbody>
</table>