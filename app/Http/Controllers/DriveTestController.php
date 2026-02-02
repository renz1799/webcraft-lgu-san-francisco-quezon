<?php

namespace App\Http\Controllers;

use App\Http\Requests\Drive\UploadDriveFileRequest;
use App\Services\Contracts\GoogleDriveServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DriveTestController extends Controller
{
    public function __construct(
        private readonly GoogleDriveServiceInterface $drive,
    ) {}

    /**
     * Show test upload page
     */
    public function index(): View
    {
        return view('drive.test-upload');
    }

    /**
     * Handle upload
     */
    public function store(UploadDriveFileRequest $request): RedirectResponse
    {
        $file = $request->file('file');

        $meta = $this->drive->upload(
            $file,
            null,
            (bool) $request->boolean('make_public')
        );

        return back()->with('uploaded', $meta);
    }
}
