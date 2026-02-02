@extends('layouts.master')

@section('content')
<div class="container mx-auto max-w-xl">

    <h2 class="text-lg font-semibold mb-4">
        Google Drive – Test Upload
    </h2>

    @if(session('uploaded'))
        <div class="p-4 mb-4 border border-success text-success rounded">
            <p class="font-semibold">Upload successful 🎉</p>

            <ul class="text-sm mt-2 space-y-1">
                <li><strong>File ID:</strong> {{ session('uploaded.drive_file_id') }}</li>
                <li><strong>Name:</strong> {{ session('uploaded.name') }}</li>
                <li><strong>Mime:</strong> {{ session('uploaded.mime_type') }}</li>
                <li><strong>Size:</strong> {{ session('uploaded.size') }} bytes</li>

                @if(session('uploaded.web_view_link'))
                    <li>
                        <a href="{{ session('uploaded.web_view_link') }}"
                           target="_blank"
                           class="text-primary underline">
                            View in Google Drive
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('drive.test.store') }}"
          enctype="multipart/form-data"
          class="space-y-4 border p-4 rounded">

        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">File (Image / PDF)</label>
            <input type="file"
                   name="file"
                   required
                   class="block w-full border rounded p-2">
            @error('file')
                <p class="text-danger text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="make_public" value="1" id="make_public">
            <label for="make_public" class="text-sm">
                Make file public (anyone with link)
            </label>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary">
                Upload to Google Drive
            </button>
        </div>
    </form>

</div>
@endsection
