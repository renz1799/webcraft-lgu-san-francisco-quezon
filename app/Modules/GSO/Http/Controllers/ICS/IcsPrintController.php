<?php

namespace App\Modules\GSO\Http\Controllers\ICS;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\ICS\PrintIcsRequest;
use App\Modules\GSO\Models\Ics;
use App\Modules\GSO\Services\Contracts\GsoSignedDocumentArchiveServiceInterface;
use App\Modules\GSO\Services\Contracts\ICS\IcsPrintServiceInterface;
use Illuminate\Http\RedirectResponse;
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
    }

    public function print(PrintIcsRequest $request, Ics $ics): View
    {
        $payload = $this->printer->buildReport(
            icsId: (string) $ics->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        return view('gso::ics.print.index', [
            'report' => $payload['report'],
            'paperProfile' => $payload['paperProfile'],
            'filters' => $request->validated(),
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

    public function storePdf(PrintIcsRequest $request, Ics $ics): RedirectResponse
    {
        $documentNumber = trim((string) ($ics->ics_number ?? ''));

        if ($documentNumber === '') {
            throw ValidationException::withMessages([
                'ics' => ['ICS number is required before storing the signed PDF.'],
            ]);
        }

        $path = $this->printer->generatePdf(
            icsId: (string) $ics->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        try {
            $stored = $this->signedDocuments->archive('ICS', $documentNumber, $path);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'drive' => [$exception->getMessage()],
            ]);
        } finally {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        return redirect()->route('gso.ics.print', ['ics' => (string) $ics->id] + $this->printQueryParams($request))
            ->with('gso_signed_pdf_archive', $stored);
    }

    /**
     * @return array<string, mixed>
     */
    private function printQueryParams(PrintIcsRequest $request): array
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
