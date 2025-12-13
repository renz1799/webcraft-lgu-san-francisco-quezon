@extends('layouts.master')

@section('styles')


<style>
    /* Add spacing between the search bar and the table */
    #logs-table_wrapper .dataTables_filter {
        margin-bottom: 15px; /* Adjust the spacing here */
    }
    /* Customize the processing container */
div.dataTables_processing {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 200px;
    margin-left: -100px;
    margin-top: -26px;
    text-align: center;
    padding: 2px;
    background: transparent; /* Remove any default background */
}

/* Target the loading dots container */
div.dataTables_processing > div:last-child {
    position: relative;
    width: 80px;
    height: 15px;
    margin: 1em auto;
}

/* Style individual dots */
div.dataTables_processing > div:last-child > div {
    position: absolute;
    top: 0;
    width: 13px;
    height: 13px;
    border-radius: 50%;
    background: black; /* Change the dots to black */
    animation-timing-function: cubic-bezier(0, 1, 1, 0);
}

/* Apply animations for the dots */
div.dataTables_processing > div:last-child > div:nth-child(1) {
    left: 8px;
    animation: datatables-loader-1 0.6s infinite;
}

div.dataTables_processing > div:last-child > div:nth-child(2) {
    left: 8px;
    animation: datatables-loader-2 0.6s infinite;
}

div.dataTables_processing > div:last-child > div:nth-child(3) {
    left: 32px;
    animation: datatables-loader-2 0.6s infinite;
}

div.dataTables_processing > div:last-child > div:nth-child(4) {
    left: 56px;
    animation: datatables-loader-3 0.6s infinite;
}

/* Keyframes for the dots animations */
@keyframes datatables-loader-1 {
    0% {
        transform: scale(0);
    }
    100% {
        transform: scale(1);
    }
}

@keyframes datatables-loader-3 {
    0% {
        transform: scale(1);
    }
    100% {
        transform: scale(0);
    }
}

@keyframes datatables-loader-2 {
    0% {
        transform: translate(0, 0);
    }
    100% {
        transform: translate(24px, 0);
    }
}

    
</style>
@endsection

@section('content')

<!-- Page Header -->
<div class="block justify-between page-header md:flex">
    <div>
        <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">
            Login Logs
        </h3>
    </div>
    <ol class="flex items-center whitespace-nowrap min-w-0">
        <li class="text-[0.813rem] ps-[0.5rem]">
            <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                Dashboard
                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
            </a>
        </li>
        <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50" aria-current="page">
            Login Logs
        </li>
    </ol>
</div>
<!-- /Page Header -->

<div class="box">
    <div class="box-body">
        <table id="logs-table" class="table table-striped table-bordered w-full">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>User</th>
                    <th>Email (attempted)</th>
                    <th>IP Address</th>
                    <th>Device</th>
                    <th>Address</th>
                    <th>Location</th>
                    <th>Date</th>
                </tr>
            </thead>
        </table>
    </div>
</div>



@push('scripts')
<script>
     console.log('login logs script loaded');
(function () {
  const escapeHtml = (s) =>
    String(s ?? '')
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;')
      .replace(/'/g,'&#39;');

  const fmtDateTime = (iso) => {
    if (!iso) return '—';
    const d = new Date(iso);
    return isNaN(d.getTime()) ? escapeHtml(iso) : d.toLocaleString();
  };

  const truncate = (s, n = 58) => {
    if (!s) return '—';
    const str = String(s);
    return str.length > n ? escapeHtml(str.slice(0, n)) + '…' : escapeHtml(str);
  };

  $(document).ready(function () {
$('#logs-table').DataTable({
  processing: true,
  serverSide: true,
  ajax: '{{ route('logs.data') }}',
  pageLength: 20,
  lengthChange: false,
  responsive: true,
  autoWidth: false,

  // ⬇️ make the initial request sorted by Date desc
  order: [[7, 'desc']],

  columns: [
    { data: 'status', name: 'success', render: (val, t, row) =>
        t !== 'display'
          ? val
          : (val === 'success'
              ? '<span class="badge bg-success/15 text-success">Success</span>'
              : `<span class="badge bg-danger/15 text-danger">Failed</span> <span class="text-xs text-muted">— ${row.reason || ''}</span>`
            )
    },
    { data: 'user',       name: 'user' },
    { data: 'email',      name: 'email' },
    { data: 'ip_address', name: 'ip_address' },
    { data: 'device',     name: 'device' },
    { data: 'address',    name: 'address' },
    {
      data: null, name: 'location', orderable: false, searchable: false,
      render: (_, type, row) =>
        type !== 'display'
          ? (row.location_url || '')
          : (row.location_url
              ? `<a href="${row.location_url}" target="_blank" rel="noopener" class="text-primary hover:underline">Map</a>`
              : '—')
    },
    { data: 'created_at', name: 'created_at' }
  ]
});

  });
})();
</script>
@endpush
@endsection