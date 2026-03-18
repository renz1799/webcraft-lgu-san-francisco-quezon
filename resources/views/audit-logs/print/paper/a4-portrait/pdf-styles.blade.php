@include('audit-logs.print.partials.base-styles', [
    'paperProfile' => $paperProfile,
])

<style>
    @page {
        size: {{ $paperProfile['width'] ?? '210mm' }} {{ $paperProfile['height'] ?? '297mm' }};
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