<?php

namespace App\Modules\GSO\Http\Controllers\ITR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\ITR\PrintItrRequest;
use App\Modules\GSO\Models\Itr;
use App\Modules\GSO\Services\Contracts\ITR\ItrPrintServiceInterface;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ItrPrintController extends Controller
{
    public function __construct(
        private readonly ItrPrintServiceInterface $printer,
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
}


