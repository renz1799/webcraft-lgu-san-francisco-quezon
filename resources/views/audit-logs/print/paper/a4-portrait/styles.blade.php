@include('audit-logs.print.partials.base-styles', [
    'paperProfile' => $paperProfile,
])

<style>
    .audit-print-page {
        margin: 0 auto 24px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
    }

    .audit-print-page:last-child {
        margin-bottom: 0;
    }
</style>