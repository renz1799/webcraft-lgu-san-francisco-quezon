<div class="gso-air-print-footer">
    <div class="gso-air-print-footer-content">
        <div class="gso-air-print-page-number">Page {{ $pageIndex + 1 }} of {{ $totalPages }}</div>
    </div>

    @if (!empty($footerImage ?? null))
        <div class="gso-air-print-footer-image-wrap">
            <img src="{{ $footerImage }}" alt="AIR footer" class="gso-air-print-footer-image">
        </div>
    @endif
</div>
