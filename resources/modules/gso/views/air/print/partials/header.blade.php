<div class="gso-air-print-header">
    @if (!empty($headerImage ?? null))
        <img src="{{ $headerImage }}" alt="AIR header" class="gso-air-print-header-image">
    @endif

    <div class="gso-air-print-header-bar">
        <div class="gso-air-print-appendix">{{ $report['document']['appendix_label'] ?? 'Appendix 30' }}</div>
        <h1 class="gso-air-print-title">{{ $report['title'] ?? 'Acceptance and Inspection Report' }}</h1>
        @if (!empty($continuationFromPage))
            <div class="gso-air-print-flow-note">
                Continuation from Page {{ $continuationFromPage }}
            </div>
        @endif
    </div>
</div>
