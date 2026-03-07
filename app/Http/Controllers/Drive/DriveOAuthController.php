<?php

namespace App\Http\Controllers\Drive;

use App\Http\Controllers\Controller;
use App\Http\Requests\Drive\UploadDriveFileRequest;
use App\Services\Contracts\GoogleDriveOAuthServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DriveOAuthController extends Controller
{
    public function __construct(
        private readonly GoogleDriveOAuthServiceInterface $drive,
    ) {}

    public function index(Request $request): View
    {
        $userId = (string) $request->user()->id;

        return view('drive.oauth-upload', [
            'connected' => $this->drive->isConnected($userId),
        ]);
    }


    public function connect(Request $request): RedirectResponse
    {
        $url = $this->drive->getAuthUrl((string) $request->user()->id);
        return redirect()->away($url);
    }

    public function callback(Request $request): RedirectResponse
    {
        $code = (string) $request->query('code');
        $userId = (string) $request->user()->id;

        $this->drive->handleCallback($userId, $code);

        return redirect()
            ->route('drive.oauth.index')
            ->with('status', 'Google Drive connected ✅');
    }

    public function upload(UploadDriveFileRequest $request): RedirectResponse
    {
        $meta = $this->drive->upload(
            (string) $request->user()->id,
            $request->file('file'),
            null,
            (bool) $request->boolean('make_public')
        );

        return back()->with('uploaded', $meta);
    }

    public function preview(Request $request, string $fileId): Response
    {
        $userId = (string) $request->user()->id;

        $file = $this->drive->download($userId, $fileId);

        return response($file['bytes'], 200, [
            'Content-Type' => $file['mime_type'],
            'Content-Disposition' => 'inline; filename="'.$file['name'].'"',
            'Cache-Control' => 'private, max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}


