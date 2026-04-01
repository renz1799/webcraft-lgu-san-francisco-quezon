<?php

namespace App\Modules\GSO\Http\Controllers\WMR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\WMR\PrintWmrRequest;
use App\Modules\GSO\Models\Wmr;
use App\Modules\GSO\Services\Contracts\GsoSignedDocumentArchiveServiceInterface;
use App\Modules\GSO\Services\Contracts\WMR\WmrPrintServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WmrPrintController extends Controller
{
    public function __construct(
        private readonly WmrPrintServiceInterface $printer,
        private readonly GsoSignedDocumentArchiveServiceInterface $signedDocuments,
    ) {
        $this->middleware('permission:wmr.print|wmr.view|wmr.create|wmr.update|wmr.submit|wmr.approve|wmr.finalize|wmr.reopen|wmr.archive|wmr.restore|wmr.manage_items');
    }

    public function print(PrintWmrRequest $request, Wmr $wmr): View
    {
        $payload = $this->printer->buildReport(
            wmrId: (string) $wmr->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        return view('gso::wmrs.print.index', [
            'report' => $payload['report'],
            'paperProfile' => $payload['paperProfile'],
            'filters' => $request->validated(),
        ]);
    }

    public function downloadPdf(PrintWmrRequest $request, Wmr $wmr): BinaryFileResponse
    {
        $path = $this->printer->generatePdf(
            wmrId: (string) $wmr->id,
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

    public function storePdf(PrintWmrRequest $request, Wmr $wmr): RedirectResponse
    {
        $documentNumber = trim((string) ($wmr->wmr_number ?? ''));

        if ($documentNumber === '') {
            throw ValidationException::withMessages([
                'wmr' => ['WMR number is required before storing the signed PDF.'],
            ]);
        }

        $path = $this->printer->generatePdf(
            wmrId: (string) $wmr->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        try {
            $stored = $this->signedDocuments->archive('WMR', $documentNumber, $path);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'drive' => [$exception->getMessage()],
            ]);
        } finally {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        return redirect()->route('gso.wmrs.print', ['wmr' => (string) $wmr->id] + $this->printQueryParams($request))
            ->with('gso_signed_pdf_archive', $stored);
    }

    /**
     * @return array<string, mixed>
     */
    private function printQueryParams(PrintWmrRequest $request): array
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
