<?php

namespace App\Modules\GSO\Http\Controllers\RIS;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\RIS\PrintRisRequest;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Services\Contracts\RIS\RisPrintServiceInterface;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RisPrintController extends Controller
{
    public function __construct(
        private readonly RisPrintServiceInterface $risPrints,
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
}
