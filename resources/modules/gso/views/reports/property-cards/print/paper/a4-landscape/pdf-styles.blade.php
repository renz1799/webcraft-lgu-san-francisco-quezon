@include('gso::reports.property-cards.print.partials.base-styles', [
    'paperProfile' => $paperProfile,
])

<style>
    @page {
        size: {{ $paperProfile['width'] ?? '297mm' }} {{ $paperProfile['height'] ?? '210mm' }};
        margin: 0;
    }

    .gso-property-cards-page {
        margin: 0;
        page-break-after: always;
        box-shadow: none;
    }

    .gso-property-cards-page:last-child {
        page-break-after: auto;
    }
</style>
