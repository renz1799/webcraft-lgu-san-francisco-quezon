@php
    $document = $report['document'] ?? [];
@endphp

<div class="gso-ptr-print-header">
    @if (!empty($headerImage))
        <img src="{{ $headerImage }}" alt="PTR Header" class="gso-ptr-print-header-image">
    @endif

    <div class="gso-ptr-print-header-bar">
        <div class="gso-ptr-print-appendix">{{ $document['appendix_label'] ?? 'Appendix 76' }}</div>
        <h1 class="gso-ptr-print-title">{{ $document['title'] ?? 'Property Transfer Report' }}</h1>
        @if (!empty($continuationFromPage))
            <div class="gso-ptr-print-flow-note">
                Continuation from Page {{ $continuationFromPage }}
            </div>
        @endif
    </div>
</div>
