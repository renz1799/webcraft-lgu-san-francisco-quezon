@extends('layouts.master')

@section('content')
<div class="container mx-auto max-w-xl">
    <h2 class="text-lg font-semibold mb-4">Google Drive – OAuth Upload Test</h2>

    @if(session('status'))
        <div class="p-3 mb-4 border rounded text-success border-success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('drive.oauth.connect') }}" class="mb-6">
        @csrf

        @if(!$connected)
            <button class="btn btn-primary" type="submit">
                Connect Google Drive
            </button>
        @else
            <button class="btn btn-success" type="button" disabled>
                Google Drive Connected ✓
            </button>
        @endif
    </form>


        @php
            $uploaded = session('uploaded');
            $isImage = str_starts_with($uploaded['mime_type'] ?? '', 'image/');
            $isPdf = ($uploaded['mime_type'] ?? '') === 'application/pdf';
        @endphp

        @if($isImage)
            <div class="mt-4">
                <div class="text-sm font-medium mb-2 text-defaulttextcolor dark:text-white">
                    Preview
                </div>
                <img
                    src="{{ route('drive.oauth.preview', $uploaded['drive_file_id']) }}"
                    alt="Uploaded image preview"
                    class="max-w-full rounded border border-defaultborder"
                    style="max-height: 360px; object-fit: contain;"
                >
            </div>
        @elseif($isPdf)
            <div class="mt-4">
                <div class="text-sm font-medium mb-2 text-defaulttextcolor dark:text-white">
                    PDF Preview
                </div>
                <iframe
                    src="{{ route('drive.oauth.preview', $uploaded['drive_file_id']) }}"
                    class="w-full rounded border border-defaultborder"
                    style="height: 480px;"
                ></iframe>
            </div>
        @endif


    <form method="POST" action="{{ route('drive.oauth.upload') }}" enctype="multipart/form-data" class="space-y-4 border p-4 rounded">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">File (Image / PDF)</label>
            <input type="file" name="file" required class="block w-full border rounded p-2">
            @error('file') <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="make_public" value="1" id="make_public">
            <label for="make_public" class="text-sm">Make file public (anyone with link)</label>
        </div>

        <div class="flex justify-end">
            <button class="btn btn-primary" type="submit">Upload</button>
        </div>
    </form>
</div>
@endsection
