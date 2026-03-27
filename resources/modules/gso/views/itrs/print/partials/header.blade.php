@php
    $document = $report['document'] ?? [];
    $appendixLabel = trim((string) ($document['appendix_label'] ?? ''));
@endphp

<div class="gso-ptr-print-header">
    @if (!empty($headerImage))
        <img src="{{ $headerImage }}" alt="ITR Header" class="gso-ptr-print-header-image">
    @endif

    <div class="gso-ptr-print-header-bar">
        @if ($appendixLabel !== '')
            <div class="gso-ptr-print-appendix">{{ $appendixLabel }}</div>
        @endif
        <h1 class="gso-ptr-print-title">{{ $document['title'] ?? 'Inventory Transfer Report' }}</h1>
        @if (!empty($continuationFromPage))
            <div class="gso-ptr-print-flow-note">
                Continuation from Page {{ $continuationFromPage }}
            </div>
        @endif
    </div>
</div>
