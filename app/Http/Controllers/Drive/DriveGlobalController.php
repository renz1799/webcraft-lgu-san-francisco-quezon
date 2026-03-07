<?php

namespace App\Http\Controllers\Drive;

use App\Http\Controllers\Controller;
use App\Http\Requests\Drive\ConnectDriveRequest;
use App\Http\Requests\Drive\DisconnectDriveRequest;
use App\Http\Requests\Drive\UploadDriveFileRequest;
use App\Services\Contracts\GoogleDriveGlobalServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DriveGlobalController extends Controller
{
    public function __construct(
        private readonly GoogleDriveGlobalServiceInterface $drive,
    ) {}

    public function index(): View
    {
        return view('drive.global-upload', [
            'connected' => $this->drive->isConnected(),
        ]);
    }

    public function connect(ConnectDriveRequest $request): RedirectResponse
    {
        return redirect()->away($this->drive->getAuthUrl());
    }

    public function callback(Request $request): RedirectResponse
    {
        $code = (string) $request->query('code');
        $this->drive->handleCallback($code);

        return redirect()
            ->route('drive.global.index')
            ->with('status', 'Google Drive connected ✅');
    }

    public function disconnect(DisconnectDriveRequest $request): RedirectResponse
    {
        $this->drive->disconnect();

        return redirect()
            ->route('drive.global.index')
            ->with('status', 'Google Drive disconnected.');
    }

    public function upload(UploadDriveFileRequest $request): RedirectResponse
    {
        $meta = $this->drive->upload(
            $request->file('file'),
            null,
            (bool) $request->boolean('make_public')
        );

        return back()->with('uploaded', $meta);
    }
    
    public function preview(Request $request, string $fileId): Response
    {
        $file = $this->drive->download($fileId);

        return response($file['bytes'], 200, [
            'Content-Type' => $file['mime_type'],
            'Content-Disposition' => 'inline; filename="'.$file['name'].'"',
            'Cache-Control' => 'private, max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}


