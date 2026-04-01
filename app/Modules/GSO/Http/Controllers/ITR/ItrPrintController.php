<?php

namespace App\Modules\GSO\Http\Controllers\ITR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\ITR\PrintItrRequest;
use App\Modules\GSO\Models\Itr;
use App\Modules\GSO\Services\Contracts\GsoSignedDocumentArchiveServiceInterface;
use App\Modules\GSO\Services\Contracts\ITR\ItrPrintServiceInterface;
use Illuminate\Http\RedirectResponse;
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

        return view('gso::itrs.print.index', [
            'report' => $payload['report'],
            'paperProfile' => $payload['paperProfile'],
            'filters' => $request->validated(),
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

    public function storePdf(PrintItrRequest $request, Itr $itr): RedirectResponse
    {
        $documentNumber = trim((string) ($itr->itr_number ?? ''));

        if ($documentNumber === '') {
            throw ValidationException::withMessages([
                'itr' => ['ITR number is required before storing the signed PDF.'],
            ]);
        }

        $path = $this->printer->generatePdf(
            itrId: (string) $itr->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        try {
            $stored = $this->signedDocuments->archive('ITR', $documentNumber, $path);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'drive' => [$exception->getMessage()],
            ]);
        } finally {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        return redirect()->route('gso.itrs.print', ['itr' => (string) $itr->id] + $this->printQueryParams($request))
            ->with('gso_signed_pdf_archive', $stored);
    }

    /**
     * @return array<string, mixed>
     */
    private function printQueryParams(PrintItrRequest $request): array
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

