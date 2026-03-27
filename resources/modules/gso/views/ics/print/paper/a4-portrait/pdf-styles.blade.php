@include('gso::ics.print.partials.base-styles', [
    'paperProfile' => $paperProfile,
])

<style>
    @page {
        size: {{ $paperProfile['width'] ?? '210mm' }} {{ $paperProfile['height'] ?? '297mm' }};
        margin: 0;
    }

    .gso-ics-print-page {
        margin: 0;
        page-break-after: always;
        box-shadow: none;
    }

    .gso-ics-print-page:last-child {
        page-break-after: auto;
    }
</style>
