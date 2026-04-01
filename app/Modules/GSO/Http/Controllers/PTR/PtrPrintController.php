<?php

namespace App\Modules\GSO\Http\Controllers\PTR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\PTR\PrintPtrRequest;
use App\Modules\GSO\Models\Ptr;
use App\Modules\GSO\Services\Contracts\GsoSignedDocumentArchiveServiceInterface;
use App\Modules\GSO\Services\Contracts\PTR\PtrPrintServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PtrPrintController extends Controller
{
    public function __construct(
        private readonly PtrPrintServiceInterface $printer,
        private readonly GsoSignedDocumentArchiveServiceInterface $signedDocuments,
    ) {
        $this->middleware('permission:ptr.print|ptr.view|ptr.create|ptr.update|ptr.submit|ptr.finalize|ptr.reopen|ptr.archive|ptr.restore|ptr.manage_items');
    }

    public function print(PrintPtrRequest $request, Ptr $ptr): View
    {
        $payload = $this->printer->buildReport(
            ptrId: (string) $ptr->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        return view('gso::ptrs.print.index', [
            'report' => $payload['report'],
            'paperProfile' => $payload['paperProfile'],
            'filters' => $request->validated(),
        ]);
    }

    public function downloadPdf(PrintPtrRequest $request, Ptr $ptr): BinaryFileResponse
    {
        $path = $this->printer->generatePdf(
            ptrId: (string) $ptr->id,
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

    public function storePdf(PrintPtrRequest $request, Ptr $ptr): RedirectResponse
    {
        $documentNumber = trim((string) ($ptr->ptr_number ?? ''));

        if ($documentNumber === '') {
            throw ValidationException::withMessages([
                'ptr' => ['PTR number is required before storing the signed PDF.'],
            ]);
        }

        $path = $this->printer->generatePdf(
            ptrId: (string) $ptr->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        try {
            $stored = $this->signedDocuments->archive('PTR', $documentNumber, $path);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'drive' => [$exception->getMessage()],
            ]);
        } finally {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        return redirect()->route('gso.ptrs.print', ['ptr' => (string) $ptr->id] + $this->printQueryParams($request))
            ->with('gso_signed_pdf_archive', $stored);
    }

    /**
     * @return array<string, mixed>
     */
    private function printQueryParams(PrintPtrRequest $request): array
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
