@php
    $scope = $report['document']['filters'] ?? [];
@endphp

<div class="gso-property-cards-page gso-property-cards-page--empty">
    <div class="gso-property-cards-empty">
        <div class="gso-property-cards-empty__eyebrow">Property Cards</div>
        <h2 class="gso-property-cards-empty__title">No inventory items matched the current batch filters.</h2>
        <p class="gso-property-cards-empty__copy">
            Adjust the report filters in the panel, then refresh the preview.
        </p>

        <div class="gso-property-cards-empty__scope">
            <div><strong>Department:</strong> {{ $scope['department'] ?? 'All Departments' }}</div>
            <div><strong>Item:</strong> {{ $scope['item'] ?? 'All Items' }}</div>
            <div><strong>Fund Source:</strong> {{ $scope['fund_source'] ?? 'All Fund Sources' }}</div>
            <div><strong>Classification:</strong> {{ $scope['classification'] ?? 'All Classes' }}</div>
            <div><strong>Inventory Status:</strong> {{ $scope['inventory_status'] ?? 'All Statuses' }}</div>
        </div>
    </div>
</div>
