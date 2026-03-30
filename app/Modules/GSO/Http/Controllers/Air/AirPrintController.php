<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\PrintAirRequest;
use App\Modules\GSO\Services\Contracts\Air\AirPrintServiceInterface;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AirPrintController extends Controller
{
    public function __construct(
        private readonly AirPrintServiceInterface $prints,
    ) {
        $this->middleware('permission:air.print|air.view|air.update');
    }

    public function preview(PrintAirRequest $request, string $air): View
    {
        $payload = $this->prints->buildReport(
            airId: $air,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        return view('gso::air.print.index', [
            'report' => $payload['report'],
            'paperProfile' => $payload['paperProfile'],
            'filters' => $request->validated(),
        ]);
    }

    public function downloadPdf(PrintAirRequest $request, string $air): BinaryFileResponse
    {
        $path = $this->prints->generatePdf(
            airId: $air,
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
