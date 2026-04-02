@php
    $archiveActor = auth()->user();
    $archiveAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
    $resolvedDocumentNumber = trim((string) ($documentNumber ?? ''));
    $resolvedRouteParams = is_array($routeParams ?? null) ? $routeParams : [];
    $resolvedDocumentType = trim((string) ($documentType ?? 'Document'));
    $resolvedUploadPermission = trim((string) ($uploadPermission ?? ''));
    $resolvedArchive = is_array($archiveRecord ?? null) ? $archiveRecord : null;
    $archiveReady = $resolvedDocumentNumber !== '';
    $archiveExists = $archiveReady && $resolvedArchive !== null;
    $canUploadArchive = $resolvedUploadPermission === ''
        ? true
        : ((bool) $archiveActor && $archiveAuthorizer->allowsPermission($archiveActor, $resolvedUploadPermission));
    $archiveUrl = $archiveReady
        ? route($archiveRoute, $resolvedRouteParams)
        : null;
    $archiveViewUrl = $archiveReady && filled($archiveViewRoute ?? null)
        ? route($archiveViewRoute, $resolvedRouteParams)
        : null;
    $resolvedFileName = trim((string) ($resolvedArchive['file_name'] ?? ($resolvedDocumentNumber !== '' ? ($resolvedDocumentNumber . '.pdf') : '')));
    $resolvedFolderPath = trim((string) ($resolvedArchive['folder_path'] ?? ''));
    $resolvedCreatedTime = trim((string) ($resolvedArchive['created_time'] ?? ''));
@endphp

<div
    data-print-archive-controls="1"
    data-print-archive-state="{{ $archiveExists ? 'uploaded' : 'empty' }}"
>
    @if ($archiveReady)
        <button
            type="button"
            class="ti-btn btn-wave ti-btn-outline-primary label-ti-btn w-full text-center {{ $archiveExists ? '' : 'hidden' }}"
            data-print-archive-view="1"
            data-print-archive-view-url="{{ $archiveViewUrl }}"
            data-print-archive-document-type="{{ $resolvedDocumentType }}"
            data-print-archive-document-number="{{ $resolvedDocumentNumber }}"
            @disabled(! $archiveExists)
        >
            <i class="ri-file-search-line label-ti-btn-icon me-2"></i>
            View Signed Document
        </button>

        @if ($canUploadArchive)
            <button
                type="button"
                class="ti-btn btn-wave {{ $archiveExists ? 'ti-btn-outline-warning' : 'ti-btn-outline-success' }} label-ti-btn w-full text-center {{ $archiveExists ? 'mt-2' : '' }}"
                data-print-archive-upload="1"
                data-print-archive-url="{{ $archiveUrl }}"
                data-print-archive-document-type="{{ $resolvedDocumentType }}"
                data-print-archive-document-number="{{ $resolvedDocumentNumber }}"
            >
                <i class="{{ $archiveExists ? 'ri-upload-cloud-2-line' : 'ri-cloud-line' }} label-ti-btn-icon me-2"></i>
                {{ $archiveExists ? 'Replace Signed PDF' : 'Upload Signed PDF' }}
            </button>
        @endif

        <p class="text-xs mt-2 {{ $archiveExists ? 'text-success' : 'text-muted' }}" data-print-archive-status="1">
            @if ($archiveExists)
                Signed PDF available as
                <span class="font-medium">{{ $resolvedFileName }}</span>
                @if ($resolvedFolderPath !== '')
                    under
                    <span class="font-medium">{{ $resolvedFolderPath }}</span>
                @endif
                @if ($resolvedCreatedTime !== '')
                    <span class="block mt-1 text-muted">Uploaded {{ $resolvedCreatedTime }}</span>
                @endif
            @elseif(! $canUploadArchive)
                Signed PDF upload is available only to users with the document archive permission for {{ $resolvedDocumentType }}.
            @else
                Upload the scanned signed PDF. It will be stored in Google Drive as
                <span class="font-medium">{{ $resolvedDocumentNumber }}.pdf</span>.
            @endif
        </p>
    @else
        <button
            type="button"
            class="ti-btn btn-wave ti-btn-outline-success label-ti-btn w-full text-center"
            disabled
        >
            <i class="ri-cloud-line label-ti-btn-icon me-2"></i>
            Upload Signed PDF
        </button>

        <p class="text-xs text-warning mt-2" data-print-archive-status="1">
            Generate and save the {{ $resolvedDocumentType }} number first before uploading the signed PDF.
        </p>
    @endif
</div>
