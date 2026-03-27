@php
    $document = $report['document'] ?? [];
@endphp

<div class="gso-ris-print-header">
    @if (!empty($headerImage))
        <img src="{{ $headerImage }}" alt="RIS Header" class="gso-ris-print-header-image">
    @endif

    <div class="gso-ris-print-header-bar">
        <div class="gso-ris-print-appendix">{{ $document['appendix_label'] ?? 'Appendix 48' }}</div>
        <h1 class="gso-ris-print-title">{{ $document['title'] ?? 'Requisition and Issue Slip' }}</h1>
        @if (!empty($continuationFromPage))
            <div class="gso-ris-print-flow-note">
                Continuation from Page {{ $continuationFromPage }}
            </div>
        @endif
    </div>
</div>
