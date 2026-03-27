@php
    $document = $report['document'] ?? [];
@endphp

<div class="gso-ics-print-header">
    @if (!empty($headerImage))
        <img src="{{ $headerImage }}" alt="ICS Header" class="gso-ics-print-header-image">
    @endif

    <div class="gso-ics-print-header-bar">
        <div class="gso-ics-print-appendix">{{ $document['appendix_label'] ?? 'Appendix 59' }}</div>
        <h1 class="gso-ics-print-title">{{ $document['title'] ?? 'Inventory Custodian Slip' }}</h1>
        @if (!empty($continuationFromPage))
            <div class="gso-ics-print-flow-note">
                Continuation from Page {{ $continuationFromPage }}
            </div>
        @endif
    </div>
</div>
