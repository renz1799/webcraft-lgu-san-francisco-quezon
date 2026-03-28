@include('gso::reports.rspi.print.partials.base-styles', [
    'paperProfile' => $paperProfile,
])

<style>
    .gso-rspi-print-page {
        margin: 0 auto 24px;
        box-shadow: 0 24px 46px rgba(15, 23, 42, 0.14);
    }

    .gso-rspi-print-page:last-child {
        margin-bottom: 0;
    }
</style>
