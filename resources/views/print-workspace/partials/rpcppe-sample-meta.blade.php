@php
  $signatories = $report['signatories'] ?? [];
@endphp

<table class="rpcppe-meta-table">
  <tr>
    <td style="width:50%;">
      <span class="rpcppe-meta-label">Entity Name:</span>
      <span class="rpcppe-meta-value long">{{ $report['entity_name'] ?? 'Local Government Unit' }}</span>
    </td>
    <td style="width:50%;">
      <span class="rpcppe-meta-label">Fund Cluster:</span>
      <span class="rpcppe-meta-value medium">{{ $report['fund_cluster'] ?? 'All / Multiple' }}</span>
    </td>
  </tr>
  <tr>
    <td>
      <span class="rpcppe-meta-label">Fund Source:</span>
      <span class="rpcppe-meta-value long">{{ $report['fund_source'] ?? 'All Fund Sources' }}</span>
    </td>
    <td>
      <span class="rpcppe-meta-label">As of:</span>
      <span class="rpcppe-meta-value medium">{{ $report['as_of_label'] ?? '' }}</span>
    </td>
  </tr>
  <tr>
    <td>
      <span class="rpcppe-meta-label">Office:</span>
      <span class="rpcppe-meta-value long">{{ $report['department'] ?? 'All Offices' }}</span>
    </td>
    <td>
      <span class="rpcppe-meta-label">Accountable Officer:</span>
      <span class="rpcppe-meta-value medium">{{ $report['accountable_officer'] ?? 'All Accountable Officers' }}</span>
    </td>
  </tr>
  <tr>
    <td colspan="2" class="rpcppe-accountability-copy">
      Signatory accountability:
      <span class="rpcppe-meta-value medium">{{ $signatories['accountable_officer_name'] ?? '' }}</span>
      <span class="rpcppe-meta-value medium">{{ $signatories['accountable_officer_designation'] ?? '' }}</span>
    </td>
  </tr>
</table>
