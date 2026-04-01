<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\PrintAirRequest;
use App\Modules\GSO\Http\Requests\Print\StoreSignedDocumentPdfRequest;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Services\Contracts\Air\AirPrintServiceInterface;
use App\Modules\GSO\Services\Contracts\GsoSignedDocumentArchiveServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AirPrintController extends Controller
{
    public function __construct(
        private readonly AirPrintServiceInterface $prints,
        private readonly GsoSignedDocumentArchiveServiceInterface $signedDocuments,
    ) {
        $this->middleware('permission:air.print|air.view|air.update');
    }

    public function preview(PrintAirRequest $request, string $air): View
    {
        $payload = $this->prints->buildReport(
            airId: $air,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );
        $documentNumber = trim((string) data_get($payload, 'report.document.air_no', ''));

        return view('gso::air.print.index', [
            'report' => $payload['report'],
            'paperProfile' => $payload['paperProfile'],
            'filters' => $request->validated(),
            'signedArchive' => $documentNumber !== ''
                ? $this->signedDocuments->findArchived('AIR', $documentNumber)
                : null,
        ]);
    }

    public function downloadPdf(PrintAirRequest $request, string $air): BinaryFileResponse
    {
        $path = $this->prints->generatePdf(
            airId: $air,
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

    public function storePdf(StoreSignedDocumentPdfRequest $request, string $air): RedirectResponse|JsonResponse
    {
        $record = Air::query()
            ->withTrashed()
            ->select(['id', 'air_number'])
            ->findOrFail($air);

        $documentNumber = trim((string) ($record->air_number ?? ''));

        if ($documentNumber === '') {
            throw ValidationException::withMessages([
                'air' => ['AIR number is required before storing the signed PDF.'],
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
            $stored = $this->signedDocuments->archive('AIR', $documentNumber, $path);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'drive' => [$exception->getMessage()],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Signed AIR PDF uploaded to Google Drive.',
                'archive' => $stored,
            ]);
        }

        return redirect()->route('gso.air.print', ['air' => (string) $record->id] + $this->printQueryParams($request))
            ->with('gso_signed_pdf_archive', $stored);
    }

    public function viewArchivedPdf(string $air): \Illuminate\Http\Response
    {
        $record = Air::query()
            ->withTrashed()
            ->select(['id', 'air_number'])
            ->findOrFail($air);

        $documentNumber = trim((string) ($record->air_number ?? ''));

        if ($documentNumber === '') {
            abort(404, 'Signed AIR PDF is not available yet.');
        }

        try {
            $download = $this->signedDocuments->downloadArchived('AIR', $documentNumber);
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
