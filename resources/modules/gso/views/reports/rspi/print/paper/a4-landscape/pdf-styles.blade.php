@include('gso::reports.rspi.print.partials.base-styles', [
    'paperProfile' => $paperProfile,
])

<style>
    @page {
        size: {{ $paperProfile['width'] ?? '297mm' }} {{ $paperProfile['height'] ?? '210mm' }};
        margin: 0;
    }

    .gso-rspi-print-page {
        margin: 0;
        page-break-after: always;
        box-shadow: none;
    }

    .gso-rspi-print-page:last-child {
        page-break-after: auto;
    }
</style>
