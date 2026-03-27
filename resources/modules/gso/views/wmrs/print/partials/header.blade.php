@php
    $document = $report['document'] ?? [];
    $appendixLabel = trim((string) ($document['appendix_label'] ?? ''));
@endphp

<div class="gso-wmr-print-header">
    @if (!empty($headerImage))
        <img src="{{ $headerImage }}" alt="WMR Header" class="gso-wmr-print-header-image">
    @endif

    <div class="gso-wmr-print-header-bar">
        @if ($appendixLabel !== '')
            <div class="gso-wmr-print-appendix">{{ $appendixLabel }}</div>
        @endif
        <h1 class="gso-wmr-print-title">{{ $document['title'] ?? 'Waste Materials Report' }}</h1>
        @if (!empty($continuationFromPage))
            <div class="gso-wmr-print-flow-note">
                Continuation from Page {{ $continuationFromPage }}
            </div>
        @endif
    </div>
</div>
