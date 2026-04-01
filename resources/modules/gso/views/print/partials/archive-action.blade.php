@php
    $resolvedDocumentNumber = trim((string) ($documentNumber ?? ''));
    $resolvedRouteParams = is_array($routeParams ?? null) ? $routeParams : [];
    $resolvedPdfParams = is_array($pdfParams ?? null) ? $pdfParams : [];
    $resolvedDocumentType = trim((string) ($documentType ?? 'Document'));
    $archiveReady = $resolvedDocumentNumber !== '';
    $archiveUrl = $archiveReady
        ? route($archiveRoute, array_merge($resolvedRouteParams, $resolvedPdfParams))
        : null;
@endphp

@if ($archiveReady)
    <a
        href="{{ $archiveUrl }}"
        class="ti-btn btn-wave ti-btn-outline-success label-ti-btn w-full text-center"
    >
        <i class="ri-cloud-line label-ti-btn-icon me-2"></i>
        Store Signed PDF
    </a>
@else
    <button
        type="button"
        class="ti-btn btn-wave ti-btn-outline-success label-ti-btn w-full text-center"
        disabled
    >
        <i class="ri-cloud-line label-ti-btn-icon me-2"></i>
        Store Signed PDF
    </button>
@endif

@unless ($archiveReady)
    <p class="text-xs text-warning mt-2">
        Generate and save the {{ $resolvedDocumentType }} number first before archiving the signed PDF.
    </p>
@endunless
