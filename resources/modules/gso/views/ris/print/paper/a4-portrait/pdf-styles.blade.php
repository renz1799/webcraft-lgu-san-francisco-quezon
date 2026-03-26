@include('gso::ris.print.partials.base-styles', [
    'paperProfile' => $paperProfile,
])

<style>
    @page {
        size: {{ $paperProfile['width'] ?? '210mm' }} {{ $paperProfile['height'] ?? '297mm' }};
        margin: 0;
    }

    .gso-ris-print-page {
        margin: 0;
        page-break-after: always;
        box-shadow: none;
    }

    .gso-ris-print-page:last-child {
        page-break-after: auto;
    }
</style>
