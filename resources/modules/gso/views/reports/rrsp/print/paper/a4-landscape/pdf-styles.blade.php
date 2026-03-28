@include('gso::reports.rrsp.print.partials.base-styles', [
    'paperProfile' => $paperProfile,
])

<style>
    @page {
        size: {{ $paperProfile['width'] ?? '297mm' }} {{ $paperProfile['height'] ?? '210mm' }};
        margin: 0;
    }

    .gso-rrsp-print-page {
        margin: 0;
        page-break-after: always;
        box-shadow: none;
    }

    .gso-rrsp-print-page:last-child {
        page-break-after: auto;
    }
</style>
