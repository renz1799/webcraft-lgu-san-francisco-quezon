@php
    $archive = session('gso_signed_pdf_archive');
    $expectedType = strtoupper(trim((string) ($documentType ?? '')));
    $matchesType = is_array($archive)
        && strtoupper(trim((string) ($archive['document_type'] ?? ''))) === $expectedType;
    $documentErrorKey = strtolower($expectedType);
    $archiveError = $errors->first('signed_pdf')
        ?: $errors->first('drive')
        ?: $errors->first($documentErrorKey);
@endphp

@if ($archiveError !== '')
    <div class="mb-4 rounded border border-danger/20 bg-danger/10 px-4 py-3 text-sm text-danger">
        {{ $archiveError }}
    </div>
@endif

@if ($matchesType)
    <div class="mb-4 rounded border border-success/20 bg-success/10 px-4 py-3 text-sm text-success">
        <div class="font-semibold">Signed PDF uploaded to Google Drive.</div>
        <div class="mt-1">
            <span class="font-medium">{{ $archive['file_name'] ?? (($archive['document_number'] ?? 'Document') . '.pdf') }}</span>
            was stored under
            <span class="font-medium">{{ $archive['folder_path'] ?? 'the configured document folder' }}</span>.
        </div>
        @if (! empty($archive['replaced_existing'] ?? false))
            <div class="mt-1">An older archived file with the same document number was replaced.</div>
        @endif
    </div>
@endif
