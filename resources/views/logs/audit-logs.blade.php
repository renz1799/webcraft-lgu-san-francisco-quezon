@extends('layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">

  <style>
    /* Page spacing */
    .page-header { margin-bottom: 1rem; }

    /* Filter form alignment */
    .audit-filters { align-items: end; }
    .audit-filters .form-control { height: 42px; }
    .audit-filters .ti-btn { height: 42px; }

    /* ✅ Restore normal box-body padding (this fixes the "broken" look) */
    .audit-table-wrap { padding: 1rem; }

    /* ✅ Make the Tabulator table feel like it is inside a bordered container */
    .audit-table-bordered {
      border: 1px solid rgba(0,0,0,.06);
      border-radius: 0.75rem;
      overflow: hidden;
      background: #fff;
    }

    /* Keep tabulator itself from adding weird outer gaps */
    #activity-table .tabulator {
      border: 0;
      border-radius: 0;
    }

    /* Tabulator footer spacing */
    #activity-table .tabulator-footer {
      padding: 0.75rem 1rem;
    }

    /* Info row aligned with the bordered container padding */
    .audit-info-row {
      padding: 0.5rem 0.25rem 0;
    }
  </style>
@endsection

@section('content')
<div class="block justify-between page-header md:flex">
  <div>
    <h3 class="text-[1.125rem] font-semibold">System Activity</h3>
  </div>
</div>

<div class="box mb-4">
  <div class="box-body">
    <form method="GET" class="grid grid-cols-12 gap-3 audit-filters">
      <input
        type="text"
        name="action"
        value="{{ $filters['action'] ?? '' }}"
        placeholder="action e.g. user.role.changed"
        class="form-control xl:col-span-4 col-span-12"
      >

      <input
        type="text"
        name="actor_id"
        value="{{ $filters['actor_id'] ?? '' }}"
        placeholder="actor uuid"
        class="form-control xl:col-span-3 col-span-12"
      >

      <input
        type="date"
        name="date_from"
        value="{{ $filters['date_from'] ?? '' }}"
        class="form-control xl:col-span-2 col-span-6"
      >

      <input
        type="date"
        name="date_to"
        value="{{ $filters['date_to'] ?? '' }}"
        class="form-control xl:col-span-2 col-span-6"
      >

      <button class="ti-btn ti-btn-primary-full !rounded-full btn-wave xl:col-span-1 col-span-12">
        Filter
      </button>
    </form>
  </div>
</div>

<div class="box">
  <div class="box-header">
    <h6 class="text-[1rem] font-semibold box-title">Recent Activity</h6>
  </div>

  <div class="box-body audit-table-wrap">
    {{-- ✅ Same idea as your Login Logs: overflow + bordered wrapper --}}
    <div class="overflow-auto audit-table-bordered">
      <div id="activity-table"
           data-endpoint="{{ route('audit-logs.data') }}"
           data-restore-endpoint="{{ route('audit.restore') }}"
           class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
    </div>

    {{-- ✅ Info row (like login logs) --}}
    <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097] audit-info-row">
      <div id="activity-info">—</div>
    </div>
  </div>
</div>

@push('scripts')
  <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>
  @vite('resources/js/logs-tabulator.js')
  @vite('resources/js/logs.js')
@endpush
@endsection
