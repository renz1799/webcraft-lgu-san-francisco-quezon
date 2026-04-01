<?php

namespace App\Modules\GSO\Http\Controllers\RIS;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\RIS\PrintRisRequest;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Services\Contracts\GsoSignedDocumentArchiveServiceInterface;
use App\Modules\GSO\Services\Contracts\RIS\RisPrintServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RisPrintController extends Controller
{
    public function __construct(
        private readonly RisPrintServiceInterface $risPrints,
        private readonly GsoSignedDocumentArchiveServiceInterface $signedDocuments,
    ) {
        $this->middleware('permission:ris.print|ris.view|ris.create|ris.update|ris.submit|ris.approve|ris.reject|ris.reopen|ris.revert|ris.archive|ris.restore|ris.manage_items|ris.generate_from_air');
    }

    public function print(PrintRisRequest $request, Ris $ris): View
    {
        $payload = $this->risPrints->buildReport(
            risId: (string) $ris->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        return view('gso::ris.print.index', [
            'report' => $payload['report'],
            'paperProfile' => $payload['paperProfile'],
            'filters' => $request->validated(),
        ]);
    }

    public function downloadPdf(PrintRisRequest $request, Ris $ris): BinaryFileResponse
    {
        $path = $this->risPrints->generatePdf(
            risId: (string) $ris->id,
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

    public function storePdf(PrintRisRequest $request, Ris $ris): RedirectResponse
    {
        $documentNumber = trim((string) ($ris->ris_number ?? ''));

        if ($documentNumber === '') {
            throw ValidationException::withMessages([
                'ris' => ['RIS number is required before storing the signed PDF.'],
            ]);
        }

        $path = $this->risPrints->generatePdf(
            risId: (string) $ris->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        try {
            $stored = $this->signedDocuments->archive('RIS', $documentNumber, $path);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'drive' => [$exception->getMessage()],
            ]);
        } finally {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        return redirect()->route('gso.ris.print', ['ris' => (string) $ris->id] + $this->printQueryParams($request))
            ->with('gso_signed_pdf_archive', $stored);
    }

    /**
     * @return array<string, mixed>
     */
    private function printQueryParams(PrintRisRequest $request): array
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
