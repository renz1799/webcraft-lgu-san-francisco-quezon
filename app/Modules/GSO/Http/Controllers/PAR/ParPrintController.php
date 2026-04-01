<?php

namespace App\Modules\GSO\Http\Controllers\PAR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\PAR\PrintParRequest;
use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Services\Contracts\GsoSignedDocumentArchiveServiceInterface;
use App\Modules\GSO\Services\Contracts\PAR\ParPrintServiceInterface;
use Illuminate\Http\RedirectResponse;
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

        return view('gso::pars.print.index', [
            'report' => $payload['report'],
            'paperProfile' => $payload['paperProfile'],
            'filters' => $request->validated(),
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

    public function storePdf(PrintParRequest $request, Par $par): RedirectResponse
    {
        $documentNumber = trim((string) ($par->par_number ?? ''));

        if ($documentNumber === '') {
            throw ValidationException::withMessages([
                'par' => ['PAR number is required before storing the signed PDF.'],
            ]);
        }

        $path = $this->printer->generatePdf(
            parId: (string) $par->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        try {
            $stored = $this->signedDocuments->archive('PAR', $documentNumber, $path);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'drive' => [$exception->getMessage()],
            ]);
        } finally {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        return redirect()->route('gso.pars.print', ['par' => (string) $par->id] + $this->printQueryParams($request))
            ->with('gso_signed_pdf_archive', $stored);
    }

    /**
     * @return array<string, mixed>
     */
    private function printQueryParams(PrintParRequest $request): array
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
