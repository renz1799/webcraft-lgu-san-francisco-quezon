<?php

namespace App\Modules\GSO\Http\Controllers\PAR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\PAR\PrintParRequest;
use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Services\Contracts\PAR\ParPrintServiceInterface;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParPrintController extends Controller
{
    public function __construct(
        private readonly ParPrintServiceInterface $printer,
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

        return response()->download(
            file: $path,
            name: basename($path),
            headers: ['Content-Type' => 'application/pdf'],
        )->deleteFileAfterSend(true);
    }
}
