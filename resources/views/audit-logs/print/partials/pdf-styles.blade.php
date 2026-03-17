@include('audit-logs.print.partials.base-styles')

<style>
    @page {
        size: A4 portrait;
        margin: 0;
    }

    .audit-print-page {
        margin: 0;
        page-break-after: always;
    }

    .audit-print-page:last-child {
        page-break-after: auto;
    }
</style>