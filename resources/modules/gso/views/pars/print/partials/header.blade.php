@php
    $document = $report['document'] ?? [];
@endphp

<div class="gso-par-print-header">
    @if (!empty($headerImage))
        <img src="{{ $headerImage }}" alt="PAR Header" class="gso-par-print-header-image">
    @endif

    <div class="gso-par-print-header-bar">
        <div class="gso-par-print-appendix">{{ $document['appendix_label'] ?? 'Appendix 71' }}</div>
        <h1 class="gso-par-print-title">{{ $document['title'] ?? 'Property Acknowledgement Receipt' }}</h1>
        @if (!empty($continuationFromPage))
            <div class="gso-par-print-flow-note">
                Continuation from Page {{ $continuationFromPage }}
            </div>
        @endif
    </div>
</div>
