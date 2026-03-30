<?php

namespace App\Modules\GSO\Http\Controllers\PTR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\PTR\PrintPtrRequest;
use App\Modules\GSO\Models\Ptr;
use App\Modules\GSO\Services\Contracts\PTR\PtrPrintServiceInterface;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PtrPrintController extends Controller
{
    public function __construct(
        private readonly PtrPrintServiceInterface $printer,
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

        return response()->download(
            file: $path,
            name: basename($path),
            headers: ['Content-Type' => 'application/pdf'],
        )->deleteFileAfterSend(true);
    }
}
