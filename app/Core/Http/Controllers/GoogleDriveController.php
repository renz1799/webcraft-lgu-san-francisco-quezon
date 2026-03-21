<?php

namespace App\Core\Http\Controllers;

use App\Core\Http\Requests\Drive\ConnectDriveRequest;
use App\Core\Http\Requests\Drive\DisconnectDriveRequest;
use App\Core\Http\Requests\Drive\UploadDriveFileRequest;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveConnectionServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class GoogleDriveController extends Controller
{
    public function __construct(
        private readonly GoogleDriveConnectionServiceInterface $connection,
        private readonly GoogleDriveFileServiceInterface $files,
    ) {}

    public function index(): View
    {
        return view('drive.index', [
            'connected' => $this->connection->isConnected(),
        ]);
    }

    public function connect(ConnectDriveRequest $request): RedirectResponse
    {
        return redirect()->away($this->connection->getAuthUrl());
    }

    public function callback(Request $request): RedirectResponse
    {
        $code = (string) $request->query('code');

        $this->connection->handleCallback($code, (string) $request->user()->id);

        return redirect()
            ->route('drive.index')
            ->with('status', 'Google Drive connected.');
    }

    public function disconnect(DisconnectDriveRequest $request): RedirectResponse
    {
        $this->connection->disconnect();

        return redirect()
            ->route('drive.index')
            ->with('status', 'Google Drive disconnected.');
    }

    public function upload(UploadDriveFileRequest $request): RedirectResponse
    {
        $meta = $this->files->upload(
            $request->file('file'),
            null,
            (bool) $request->boolean('make_public'),
        );

        return back()->with('uploaded', $meta);
    }

    public function preview(Request $request, string $fileId): Response
    {
        $file = $this->files->download($fileId);

        return response($file['bytes'], 200, [
            'Content-Type' => $file['mime_type'],
            'Content-Disposition' => 'inline; filename="' . $file['name'] . '"',
            'Cache-Control' => 'private, max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
