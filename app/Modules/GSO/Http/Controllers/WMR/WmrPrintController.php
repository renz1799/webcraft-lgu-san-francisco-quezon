<?php

namespace App\Modules\GSO\Http\Controllers\WMR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\WMR\PrintWmrRequest;
use App\Modules\GSO\Models\Wmr;
use App\Modules\GSO\Services\Contracts\WMR\WmrPrintServiceInterface;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WmrPrintController extends Controller
{
    public function __construct(
        private readonly WmrPrintServiceInterface $printer,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view WMR|modify WMR');
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

        return response()->download(
            file: $path,
            name: basename($path),
            headers: ['Content-Type' => 'application/pdf'],
        )->deleteFileAfterSend(true);
    }
}
