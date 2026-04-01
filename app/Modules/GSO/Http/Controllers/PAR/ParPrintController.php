<?php

namespace App\Modules\GSO\Http\Controllers\PAR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Print\StoreSignedDocumentPdfRequest;
use App\Modules\GSO\Http\Requests\PAR\PrintParRequest;
use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Services\Contracts\GsoSignedDocumentArchiveServiceInterface;
use App\Modules\GSO\Services\Contracts\PAR\ParPrintServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParPrintController extends Controller
{
    public function __construct(
        private readonly ParPrintServiceInterface $printer,
        private readonly GsoSignedDocumentArchiveServiceInterface $signedDocuments,
    ) {
        $this->middleware('permission:par.print|par.view|par.create|par.update|par.submit|par.finalize|par.reopen|par.archive|par.restore|par.manage_items');
    }

    public function print(PrintParRequest $request, Par $par): View
    {
        $payload = $this->printer->buildReport(
            parId: (string) $par->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );
        $documentNumber = trim((string) data_get($payload, 'report.document.par_no', ''));

        return view('gso::pars.print.index', [
            'report' => $payload['report'],
            'paperProfile' => $payload['paperProfile'],
            'filters' => $request->validated(),
            'signedArchive' => $documentNumber !== ''
                ? $this->signedDocuments->findArchived('PAR', $documentNumber)
                : null,
        ]);
    }

    public function downloadPdf(PrintParRequest $request, Par $par): BinaryFileResponse
    {
        $path = $this->printer->generatePdf(
            parId: (string) $par->id,
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

    public function storePdf(StoreSignedDocumentPdfRequest $request, Par $par): RedirectResponse|JsonResponse
    {
        $documentNumber = trim((string) ($par->par_number ?? ''));

        if ($documentNumber === '') {
            throw ValidationException::withMessages([
                'par' => ['PAR number is required before storing the signed PDF.'],
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
            $stored = $this->signedDocuments->archive('PAR', $documentNumber, $path);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'drive' => [$exception->getMessage()],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Signed PAR PDF uploaded to Google Drive.',
                'archive' => $stored,
            ]);
        }

        return redirect()->route('gso.pars.print', ['par' => (string) $par->id] + $this->printQueryParams($request))
            ->with('gso_signed_pdf_archive', $stored);
    }

    public function viewArchivedPdf(Par $par): \Illuminate\Http\Response
    {
        $documentNumber = trim((string) ($par->par_number ?? ''));

        if ($documentNumber === '') {
            abort(404, 'Signed PAR PDF is not available yet.');
        }

        try {
            $download = $this->signedDocuments->downloadArchived('PAR', $documentNumber);
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
