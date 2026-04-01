<?php

namespace App\Modules\GSO\Http\Controllers\ITR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Print\StoreSignedDocumentPdfRequest;
use App\Modules\GSO\Http\Requests\ITR\PrintItrRequest;
use App\Modules\GSO\Models\Itr;
use App\Modules\GSO\Services\Contracts\GsoSignedDocumentArchiveServiceInterface;
use App\Modules\GSO\Services\Contracts\ITR\ItrPrintServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ItrPrintController extends Controller
{
    public function __construct(
        private readonly ItrPrintServiceInterface $printer,
        private readonly GsoSignedDocumentArchiveServiceInterface $signedDocuments,
    ) {
        $this->middleware('permission:itr.print|itr.view|itr.create|itr.update|itr.submit|itr.finalize|itr.reopen|itr.archive|itr.restore|itr.manage_items');
    }

    public function print(PrintItrRequest $request, Itr $itr): View
    {
        $payload = $this->printer->buildReport(
            itrId: (string) $itr->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );
        $documentNumber = trim((string) data_get($payload, 'report.document.itr_no', ''));

        return view('gso::itrs.print.index', [
            'report' => $payload['report'],
            'paperProfile' => $payload['paperProfile'],
            'filters' => $request->validated(),
            'signedArchive' => $documentNumber !== ''
                ? $this->signedDocuments->findArchived('ITR', $documentNumber)
                : null,
        ]);
    }

    public function downloadPdf(PrintItrRequest $request, Itr $itr): BinaryFileResponse
    {
        $path = $this->printer->generatePdf(
            itrId: (string) $itr->id,
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

    public function storePdf(StoreSignedDocumentPdfRequest $request, Itr $itr): RedirectResponse|JsonResponse
    {
        $documentNumber = trim((string) ($itr->itr_number ?? ''));

        if ($documentNumber === '') {
            throw ValidationException::withMessages([
                'itr' => ['ITR number is required before storing the signed PDF.'],
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
            $stored = $this->signedDocuments->archive('ITR', $documentNumber, $path);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'drive' => [$exception->getMessage()],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Signed ITR PDF uploaded to Google Drive.',
                'archive' => $stored,
            ]);
        }

        return redirect()->route('gso.itrs.print', ['itr' => (string) $itr->id] + $this->printQueryParams($request))
            ->with('gso_signed_pdf_archive', $stored);
    }

    public function viewArchivedPdf(Itr $itr): \Illuminate\Http\Response
    {
        $documentNumber = trim((string) ($itr->itr_number ?? ''));

        if ($documentNumber === '') {
            abort(404, 'Signed ITR PDF is not available yet.');
        }

        try {
            $download = $this->signedDocuments->downloadArchived('ITR', $documentNumber);
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

