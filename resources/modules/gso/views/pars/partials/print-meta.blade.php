@php
  $entityName = $print['entity_name'] ?? '';
  $fundCluster = $print['fund_cluster'] ?? '';
@endphp

<table class="form-table">
  <colgroup>
    <col style="width: 93.5%;">
    <col style="width: 40%;">
  </colgroup>
  <tbody>
    {{-- Row 1: Entity + Date --}}
    <tr>
      <td>
        <span class="bold">Entity Name :</span>
        <span class="upper">{{ $entityName }}</span>
      </td>
      <td>
        <span class="bold">Date :</span>
        <span class="upper">
          {{ optional($par->issued_date)->format('m/d/Y') ?? '' }}
        </span>
      </td>
    </tr>

    {{-- Row 2: Fund Cluster + PAR No --}}
    <tr>
      <td>
        <span class="bold">Fund Cluster :</span>
        <span class="upper">{{ $fundCluster }}</span>
      </td>
      <td>
        <span class="bold">PAR No. :</span>
        <span class="upper">{{ $par->par_number ?? '' }}</span>
      </td>
    </tr>
  </tbody>
</table>