<?php

namespace App\Modules\GSO\Http\Controllers\ICS;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Print\StoreSignedDocumentPdfRequest;
use App\Modules\GSO\Http\Requests\ICS\PrintIcsRequest;
use App\Modules\GSO\Models\Ics;
use App\Modules\GSO\Services\Contracts\GsoSignedDocumentArchiveServiceInterface;
use App\Modules\GSO\Services\Contracts\ICS\IcsPrintServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IcsPrintController extends Controller
{
    public function __construct(
        private readonly IcsPrintServiceInterface $printer,
        private readonly GsoSignedDocumentArchiveServiceInterface $signedDocuments,
    ) {
        $this->middleware('permission:ics.print|ics.view|ics.create|ics.update|ics.submit|ics.finalize|ics.reopen|ics.archive|ics.restore|ics.manage_items');
        $this->middleware('permission:ics.upload_signed_pdf')->only(['storePdf']);
    }

    public function print(PrintIcsRequest $request, Ics $ics): View
    {
        $payload = $this->printer->buildReport(
            icsId: (string) $ics->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );
        $documentNumber = trim((string) data_get($payload, 'report.document.ics_no', ''));

        return view('gso::ics.print.index', [
            'report' => $payload['report'],
            'paperProfile' => $payload['paperProfile'],
            'filters' => $request->validated(),
            'signedArchive' => $documentNumber !== ''
                ? $this->signedDocuments->findArchived('ICS', $documentNumber)
                : null,
        ]);
    }

    public function downloadPdf(PrintIcsRequest $request, Ics $ics): BinaryFileResponse
    {
        $path = $this->printer->generatePdf(
            icsId: (string) $ics->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        if ($request->boolean('inline')) {
            return response()->file(
                file: $path,
                headers: ['Content-Type' => 'application/pdf'],
            )->deleteFileAfterSend(true);
        }

        return response()->download(
            file: $path,
            name: basename($path),
            headers: ['Content-Type' => 'application/pdf'],
        )->deleteFileAfterSend(true);
    }

    public function storePdf(StoreSignedDocumentPdfRequest $request, Ics $ics): RedirectResponse|JsonResponse
    {
        $documentNumber = trim((string) ($ics->ics_number ?? ''));

        if ($documentNumber === '') {
            throw ValidationException::withMessages([
                'ics' => ['ICS number is required before storing the signed PDF.'],
            ]);
        }

        $uploadedFile = $request->file('signed_pdf');
        $path = $uploadedFile?->getRealPath() ?: $uploadedFile?->path();

        if (! $uploadedFile || ! is_string($path) || trim($path) === '') {
            throw ValidationException::withMessages([
                'signed_pdf' => ['Select the scanned signed PDF to upload.'],
            ]);
        }

        $request->releaseSessionLock();

        try {
            $stored = $this->signedDocuments->archive('ICS', $documentNumber, $path);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'drive' => [$exception->getMessage()],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Signed ICS PDF uploaded to Google Drive.',
                'archive' => $stored,
            ]);
        }

        return redirect()->route('gso.ics.print', ['ics' => (string) $ics->id] + $this->printQueryParams($request))
            ->with('gso_signed_pdf_archive', $stored);
    }

    public function viewArchivedPdf(Ics $ics): \Illuminate\Http\Response
    {
        $documentNumber = trim((string) ($ics->ics_number ?? ''));

        if ($documentNumber === '') {
            abort(404, 'Signed ICS PDF is not available yet.');
        }

        try {
            $download = $this->signedDocuments->downloadArchived('ICS', $documentNumber);
        } catch (\RuntimeException $exception) {
            abort(404, $exception->getMessage());
        }

        return response($download['bytes'], 200, [
            'Content-Type' => $download['mime_type'] ?? 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . ($download['name'] ?? ($documentNumber . '.pdf')) . '"',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function printQueryParams(StoreSignedDocumentPdfRequest $request): array
    {
        return array_filter([
            'paper_profile' => $request->validated('paper_profile'),
            'rows_per_page' => $request->validated('rows_per_page'),
            'grid_rows' => $request->validated('grid_rows'),
            'last_page_grid_rows' => $request->validated('last_page_grid_rows'),
            'description_chars_per_line' => $request->validated('description_chars_per_line'),
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
